<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Mail;
use App\Mail\LaporanCustomerMail;
use App\Models\Kunjungan;
use App\Models\Laporan;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LaporanController extends Controller
{
    // Halaman daftar semua laporan yang sudah selesai / terverifikasi
    public function index(Request $request)
    {
        $user = Auth::user();
        // Mulai query dasar dan panggil relasi yang dibutuhin
        $query = Laporan::with(['kunjungan.customer', 'kunjungan.engineer.user', 'buktiPenyelesaian']);

        // Jika login sebagai Engineer, filter hanya laporannya sendiri
        if ($user->id_role == 3) {
            $query->whereHas('kunjungan.engineer', function ($q) use ($user) {
                $q->where('id_pengguna', $user->id_pengguna);
            });
        }

        // Fitur Pencarian (Cari berdasarkan Nomor Kunjungan, Nama Customer, atau Nama Engineer)
        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->whereHas('kunjungan', function($q) use ($search) {
                $q->where('nomor', 'like', '%' . $search . '%')
                  ->orWhereHas('customer', function($cq) use ($search) {
                      $cq->where('nama_perusahaan', 'like', '%' . $search . '%');
                  })
                  ->orWhereHas('engineer.user', function($eq) use ($search) {
                      $eq->where('nama', 'like', '%' . $search . '%');
                  });
            });
        }

        // Fitur Sortir (Terbaru / Terlama)
        $sort = $request->get('sort', 'terbaru');
        if ($sort == 'terlama') {
            $query->oldest('id_laporan'); // Urutkan dari yang pertama kali dibuat
        } else {
            $query->latest('id_laporan'); // Urutkan dari yang paling baru
        }

        // Pagination max 10, jangan lupa appends request biar filter pencarian nggak hilang pas pindah halaman
        $laporanList = $query->paginate(10)->appends($request->all());

        return view('laporan.index', compact('laporanList'));
    }

    // Preview PDF sebagai gambar (untuk modal preview di HP yang tidak bisa render PDF inline)
    public function previewPdf($id_kunjungan)
    {
        $kunjungan = Kunjungan::with([
            'customer',
            'engineer.user',
            'tools',
            'aktivitas',
            'dokumentasi',
            'laporan.buktiPenyelesaian'
        ])->where('nomor', $id_kunjungan)->firstOrFail();

        $aktivitas = $kunjungan->aktivitas->firstWhere('id_engineer', $kunjungan->id_engineer) ?? $kunjungan->aktivitas->last();
        $bukti = $kunjungan->laporan->buktiPenyelesaian ?? null;

        $pdf = Pdf::loadView('laporan.pdf_template', compact('kunjungan', 'aktivitas', 'bukti'))
                  ->setPaper('a4', 'portrait');

        $tmpBase = tempnam(sys_get_temp_dir(), 'pdfprev') ;
        @unlink($tmpBase);
        $pdfPath = $tmpBase . '.pdf';
        file_put_contents($pdfPath, $pdf->output());

        $images = [];
        $pngPrefix = $tmpBase . '_page';
        exec('pdftoppm -png -r 80 ' . escapeshellarg($pdfPath) . ' ' . escapeshellarg($pngPrefix) . ' 2>/dev/null');

        // Simpan sebagai file publik (1 file per kunjungan, ditimpa tiap preview)
        $publicDir = public_path('pdf_preview');
        if (!is_dir($publicDir)) mkdir($publicDir, 0755, true);
        $i = 0;
        foreach (glob($pngPrefix . '*.png') as $pngFile) {
            $i++;
            $dest = $publicDir . '/' . $id_kunjungan . '_' . $i . '.png';
            @unlink($dest);
            rename($pngFile, $dest);
            $images[] = url('pdf_preview/' . $id_kunjungan . '_' . $i . '.png') . '?t=' . time();
        }
        // Hapus sisa file halaman lama jika jumlah halaman berkurang
        for ($j = $i + 1; $j <= $i + 5; $j++) {
            @unlink($publicDir . '/' . $id_kunjungan . '_' . $j . '.png');
        }
        @unlink($pdfPath);
        @unlink($tmpBase);

        return response()->json(['images' => $images])
            ->header('Cache-Control', 'no-cache, no-store, must-revalidate, max-age=0');
    }

    // Generate dan Download / Stream PDF Service Completion Receipt
    public function downloadPdf(Request $request, $id_kunjungan)
    {
        $kunjungan = Kunjungan::with([
            'customer',
            'engineer.user',
            'tools',
            'aktivitas',
            'dokumentasi',
            'laporan.buktiPenyelesaian'
        ])->where('nomor', $id_kunjungan)->firstOrFail();

        // Ambil aktivitas LEAD engineer untuk waktu check-in/check-out dan catatan (PDF hanya pakai lead)
        $aktivitas = $kunjungan->aktivitas->firstWhere('id_engineer', $kunjungan->id_engineer) ?? $kunjungan->aktivitas->last();
        $bukti = $kunjungan->laporan->buktiPenyelesaian ?? null;

        $pdf = Pdf::loadView('laporan.pdf_template', compact('kunjungan', 'aktivitas', 'bukti'))
                  ->setPaper('a4', 'portrait');

        // Anti-cache: pastikan browser selalu ambil PDF terbaru (real-time)
        // ?download=1 -> force download; default -> inline (preview di browser)
        $disposition = $request->get('download') ? 'attachment' : 'inline';
        $filename = 'Service_Completion_Receipt_' . $kunjungan->nomor . '.pdf';
        return response()->stream(function () use ($pdf) {
            echo $pdf->output();
        }, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => $disposition . '; filename="' . $filename . '"',
            'Cache-Control' => 'no-cache, no-store, must-revalidate, max-age=0',
            'Pragma' => 'no-cache',
            'Expires' => '0',
        ]);
    }

    // =====================================================================
    // FUNGSI BARU UNTUK INTERNAL APPROVAL
    // =====================================================================
    public function updateApproval(Request $request, $id_laporan)
    {
        // 1. LOGIC SAKTI: Tendang Engineer kalau nyoba akses fitur ini
        if (Auth::user()->id_role == 3) {
            return back()->with('error', 'Akses ditolak! Engineer tidak berhak melakukan persetujuan laporan.');
        }

        // 2. Validasi inputan dari form UI
        $request->validate([
            'status_approval' => 'required|in:Disetujui,Revisi,Ditolak',
            'catatan_approval' => 'nullable|string'
        ]);

        // 3. Cari laporan berdasarkan ID, lalu update datanya
        $laporan = Laporan::findOrFail($id_laporan);
        
        $laporan->status_approval = $request->status_approval;
        $laporan->catatan_approval = $request->catatan_approval;
        
        // Catat siapa atasan (id_pengguna) yang nge-klik tombol approve/revisi
        $laporan->disetujui_oleh = Auth::user()->id_pengguna;
        
        $laporan->save();

        // Kembalikan ke halaman sebelumnya dengan pesan sukses
        return back()->with('success', 'Status persetujuan laporan berhasil diperbarui!');
    }

    // =====================================================================
    // FUNGSI BARU: Kirim Email PDF ke Customer
    // =====================================================================
    public function sendEmail($id_kunjungan)
    {
        $kunjungan = Kunjungan::with([
            'customer', 'engineer.user', 'tools', 'aktivitas', 'dokumentasi', 'laporan.buktiPenyelesaian'
        ])->where('nomor', $id_kunjungan)->firstOrFail();

        // Pastikan laporan sudah di-ACC sebelum dikirim
        if ($kunjungan->laporan->status_approval != 'Disetujui') {
            return back()->with('error', 'Gagal! Laporan harus disetujui (ACC) oleh atasan sebelum dikirim ke klien.');
        }

        // Opsional: Cek apakah customer punya email di database (asumsi kolomnya 'email')
        $emailTujuan = $kunjungan->customer->email ?? 'dummyclient@mailinator.com'; // Ganti fallback-nya kalau kolom email nggak ada

        // Ambil aktivitas LEAD engineer untuk PDF (hanya koordinat lead)
        $aktivitas = $kunjungan->aktivitas->firstWhere('id_engineer', $kunjungan->id_engineer) ?? $kunjungan->aktivitas->last();
        $bukti = $kunjungan->laporan->buktiPenyelesaian ?? null;

        // Render PDF ke dalam memory (tanpa di-download ke browser)
        $pdf = Pdf::loadView('laporan.pdf_template', compact('kunjungan', 'aktivitas', 'bukti'))
                  ->setPaper('a4', 'portrait');
        
        $pdfData = $pdf->output();

        // Eksekusi pengiriman email
        Mail::to($emailTujuan)->send(new LaporanCustomerMail($kunjungan, $pdfData));

        return back()->with('success', 'Berhasil! Email Berita Acara beserta PDF terkirim ke: ' . $emailTujuan);
    }

    // =====================================================================
    // FUNGSI BARU: Engineer Submit Revisi Laporan
    // =====================================================================
    public function submitRevisi(Request $request, $id_laporan)
    {
        // Validasi input
        $request->validate([
            'catatan_revisi' => 'required|string'
        ]);

        $laporan = Laporan::with('kunjungan.aktivitas')->findOrFail($id_laporan);

        // 1. Update catatan/deskripsi pada aktivitas kunjungan terakhir
        $aktivitas = $laporan->kunjungan->aktivitas->last();
        if ($aktivitas) {
            $aktivitas->catatan = $request->catatan_revisi;
            $aktivitas->save();
        }

        // 2. Ubah status laporan kembali menjadi Menunggu Persetujuan
        $laporan->status_approval = 'Menunggu Persetujuan';
        
        // 3. Kosongkan kembali catatan dari atasan karena sudah direvisi
        $laporan->catatan_approval = null; 
        
        $laporan->save();

        return back()->with('success', 'Laporan berhasil diperbaiki dan diajukan ulang ke Atasan!');
    }
}