<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Engineer;
use App\Models\Tool;
use App\Models\Kunjungan;
use App\Models\AktivitasPekerjaan;
use App\Models\Dokumentasi;
use App\Models\Laporan;
use App\Models\BuktiPenyelesaian;
use App\Models\Pengeluaran;
use App\Models\CustomerSite;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class KunjunganController extends Controller
{
    // 1. Tampilkan List Kunjungan
    public function index(Request $request)
    {
        $user = Auth::user();
        // OPTIMASI: Tambahkan 'site' di eager loading
        $query = Kunjungan::with(['customer', 'site', 'engineer.user', 'tools', 'supportEngineers.user']);

        // Jika engineer, filter hanya kunjungan miliknya
        if ($user->id_role == 3) {
            $engineer = Engineer::where('id_pengguna', $user->id_pengguna)->first();
            if ($engineer) {
                $query->where('id_engineer', $engineer->id_engineer);
            }
        }

        // Fitur Pencarian
        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('nomor', 'like', '%' . $search . '%')
                  ->orWhere('pekerjaan', 'like', '%' . $search . '%')
                  ->orWhereHas('customer', function($cq) use ($search) {
                      $cq->where('nama_perusahaan', 'like', '%' . $search . '%');
                  });
            });
        }

        // Fitur Sortir
        $sort = $request->get('sort', 'terbaru');
        if ($sort == 'terlama') {
            $query->oldest();
        } else {
            $query->latest();
        }

        $kunjunganList = $query->paginate(10)->appends($request->all());
        
        $customers = Customer::all();
        $engineers = Engineer::with('user')->where('status_ketersediaan', 'Tersedia')->get();
        $tools = Tool::where('status_ketersediaan', 'Tersedia')->get();

        return view('kunjungan.index', compact('kunjunganList', 'customers', 'engineers', 'tools'));
    }

    // 2. Buat Kunjungan Baru
    public function store(Request $request)
    {
        $request->validate([
            'id_customer' => 'required|exists:customers,id_customer',
            'id_site' => 'nullable|exists:customer_sites,id_site',
            'id_engineer' => 'nullable|exists:engineers,id_engineer',
            'tanggal' => 'required|date',
            'waktu' => 'required',
            'lokasi' => 'required|string',
            'pekerjaan' => 'required|string|max:150',
            'tools' => 'nullable|array',
            'tools.*' => 'exists:tools,id_tool',
            'support_engineers' => 'nullable|array|max:4',
            'support_engineers.*' => 'exists:engineers,id_engineer',
        ]);

        DB::transaction(function () use ($request) {
            $nomorKunjungan = 'VMS-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -4));

            $kunjungan = Kunjungan::create([
                'nomor' => $nomorKunjungan,
                'id_customer' => $request->id_customer,
                'id_site' => $request->id_site,
                'id_engineer' => $request->id_engineer,
                'tanggal' => $request->tanggal,
                'waktu' => $request->waktu,
                'lokasi' => $request->lokasi,
                'pekerjaan' => $request->pekerjaan,
                'status' => 'Terjadwal',
            ]);

            if ($request->filled('tools')) {
                foreach ($request->tools as $toolId) {
                    $kunjungan->tools()->attach($toolId, ['jumlah' => 1]);
                }
            }

            if ($request->filled('support_engineers')) {
                $kunjungan->supportEngineers()->attach($request->support_engineers);
            }
        });

        return redirect()->back()->with('success', 'Jadwal Kunjungan berhasil dibuat!');
    }

    // 3. Update Kunjungan
    public function update(Request $request, $id)
    {
        $kunjungan = Kunjungan::findOrFail($id);

        $request->validate([
            'id_customer' => 'required|exists:customers,id_customer',
            'id_site' => 'nullable|exists:customer_sites,id_site',
            'id_engineer' => 'nullable|exists:engineers,id_engineer',
            'tanggal' => 'required|date',
            'waktu' => 'required',
            'lokasi' => 'required|string',
            'pekerjaan' => 'required|string|max:150',
            'tools' => 'nullable|array',
            'tools.*' => 'exists:tools,id_tool',
            'support_engineers' => 'nullable|array|max:4',
            'support_engineers.*' => 'exists:engineers,id_engineer',
        ]);

        DB::transaction(function () use ($request, $kunjungan) {
            $statusBaru = $kunjungan->status == 'Reschedule' ? 'Terjadwal' : $kunjungan->status;
            $alasan = $kunjungan->status == 'Reschedule' ? null : $kunjungan->alasan_reschedule;

            $kunjungan->update([
                'id_customer' => $request->id_customer,
                'id_site' => $request->id_site,
                'id_engineer' => $request->id_engineer,
                'tanggal' => $request->tanggal,
                'waktu' => $request->waktu,
                'lokasi' => $request->lokasi,
                'pekerjaan' => $request->pekerjaan,
                'status' => $statusBaru,
                'alasan_reschedule' => $alasan,
            ]);

            if ($request->filled('tools')) {
                $syncData = [];
                foreach ($request->tools as $toolId) {
                    $syncData[$toolId] = ['jumlah' => 1];
                }
                $kunjungan->tools()->sync($syncData);
            } else {
                $kunjungan->tools()->detach();
            }

            if ($request->filled('support_engineers')) {
                $kunjungan->supportEngineers()->sync($request->support_engineers);
            } else {
                $kunjungan->supportEngineers()->detach();
            }
        });

        return redirect()->back()->with('success', 'Data Kunjungan berhasil diperbarui!');
    }

    // 4. Hapus Kunjungan
    public function destroy($id)
    {
        $kunjungan = Kunjungan::findOrFail($id);
        $kunjungan->tools()->detach();
        $kunjungan->delete();

        return redirect()->back()->with('success', 'Jadwal Kunjungan berhasil dihapus secara permanen!');
    }

    // 5. Detail Kunjungan
    public function show($id)
    {
        // OPTIMASI: Tambahkan 'site' di eager loading
        $kunjungan = Kunjungan::with([
            'customer', 
            'site',
            'engineer.user', 
            'tools', 
            'aktivitas', 
            'dokumentasi', 
            'laporan.buktiPenyelesaian',
            'pengeluaran',
            'supportEngineers.user' 
        ])->findOrFail($id);

        return view('kunjungan.show', compact('kunjungan'));
    }

    // 6. Engineer Check-in
    public function checkIn(Request $request, $id)
    {
        $kunjungan = Kunjungan::with('customer')->findOrFail($id);

        if (!in_array($kunjungan->status, ['Terjadwal', 'Dikonfirmasi'])) {
            return redirect()->back()->with('error', 'Status kunjungan tidak valid untuk dilakukan Check-in.');
        }

        $request->validate([
            'lokasi_gps' => 'required|string',
        ]);

        $coords = explode(',', str_replace(' ', '', $request->lokasi_gps));
        $lat = $coords[0] ?? null;
        $lng = $coords[1] ?? null;

        $customer = $kunjungan->customer;
        $site = $kunjungan->id_site ? \App\Models\CustomerSite::find($kunjungan->id_site) : null;

        $targetLat = $site && $site->latitude ? $site->latitude : $customer->latitude;
        $targetLng = $site && $site->longitude ? $site->longitude : $customer->longitude;

        if (!$targetLat || !$targetLng) {
            return redirect()->back()->with('error', 'Gagal Check-in! Titik GPS klien/cabang belum diatur oleh Pimpinan. Harap hubungi atasan.');
        }

        $latEngineer = (float) $lat;
        $lonEngineer = (float) $lng;
        $latTarget = (float) $targetLat;
        $lonTarget = (float) $targetLng;

        $earthRadius = 6371;
        $dLat = deg2rad($latEngineer - $latTarget);
        $dLon = deg2rad($lonEngineer - $lonTarget);

        $a = sin($dLat/2) * sin($dLat/2) + cos(deg2rad($latTarget)) * cos(deg2rad($latEngineer)) * sin($dLon/2) * sin($dLon/2);
        $c = 2 * atan2(sqrt($a), sqrt(1-$a));
        $jarakMeter = $earthRadius * $c * 1000;

        if ($jarakMeter > 100) {
            return redirect()->back()->with('error', 'Gagal Check-in! Anda berada di luar radius 100 meter dari lokasi kerja. Jarak Anda saat ini: ' . round($jarakMeter) . ' meter dari lokasi tujuan.');
        }

        $kunjungan->update([
            'status' => 'Dikerjakan',
            'check_in_latitude' => $lat,
            'check_in_longitude' => $lng
        ]);

        AktivitasPekerjaan::create([
            'id_kunjungan' => $kunjungan->id_kunjungan,
            'waktu_mulai' => now(),
            'lokasi' => $request->lokasi_gps,
            'deskripsi' => 'Engineer tiba di lokasi dan memulai pengerjaan.',
        ]);

        return redirect()->back()->with('success', 'Check-in berhasil! Jarak Anda: ' . round($jarakMeter) . ' meter dari target.');
    }

    // 7. Engineer Upload Dokumentasi
    public function uploadDokumentasi(Request $request, $id)
    {
        $request->validate([
            'kategori_foto' => 'required|in:Sebelum,Proses,Sesudah,Lainnya',
            'foto' => 'required|image|mimes:jpeg,png,jpg|max:5120',
            'keterangan' => 'nullable|string',
        ]);

        $file = $request->file('foto');
        $filename = time() . '_' . $file->getClientOriginalName();
        $file->move(public_path('uploads/dokumentasi'), $filename);

        Dokumentasi::create([
            'id_kunjungan' => $id,
            'kategori_foto' => $request->kategori_foto,
            'file_foto' => 'uploads/dokumentasi/' . $filename,
            'keterangan' => $request->keterangan,
        ]);

        return redirect()->back()->with('success', 'Foto dokumentasi berhasil diunggah!');
    }

    // 8. Simpan Pengeluaran
    public function storePengeluaran(Request $request, $id)
    {
        $request->validate([
            'jenis_biaya' => 'required|string|max:100',
            'nominal' => 'required|integer|min:0',
            'keterangan' => 'nullable|string',
            'bukti_nota' => 'nullable|image|mimes:jpeg,png,jpg|max:5120',
        ]);

        $path = null;
        if ($request->hasFile('bukti_nota')) {
            $file = $request->file('bukti_nota');
            $filename = time() . '_nota_' . $file->getClientOriginalName();
            $file->move(public_path('uploads/pengeluaran'), $filename);
            $path = 'uploads/pengeluaran/' . $filename;
        }

        Pengeluaran::create([
            'id_kunjungan' => $id,
            'jenis_biaya' => $request->jenis_biaya,
            'nominal' => $request->nominal,
            'keterangan' => $request->keterangan,
            'bukti_nota' => $path,
        ]);

        return redirect()->back()->with('success', 'Pengeluaran operasional berhasil dicatat!');
    }

    // 9. Check-out
    public function checkOut(Request $request, $id)
    {
        $kunjungan = Kunjungan::findOrFail($id);

        $request->validate([
            'catatan' => 'required|string',
            'lokasi_gps' => 'required|string',
        ]);

        $coords = explode(',', str_replace(' ', '', $request->lokasi_gps));
        $lat = $coords[0] ?? null;
        $lng = $coords[1] ?? null;

        $kunjungan->update([
            'check_out_latitude' => $lat,
            'check_out_longitude' => $lng
        ]);

        $aktivitas = AktivitasPekerjaan::where('id_kunjungan', $id)->latest()->first();
        if ($aktivitas) {
            $aktivitas->update([
                'waktu_selesai' => now(),
                'catatan' => $request->catatan,
            ]);
        }

        Laporan::firstOrCreate(
            ['id_kunjungan' => $id],
            [
                'tanggal_dibuat' => now(),
                'status_laporan' => 'Terbuat Otomatis'
            ]
        );

        return redirect()->back()->with('success', 'Check-out berhasil! Menunggu verifikasi tanda tangan customer.');
    }

    // 10. Tanda Tangan Customer
    public function verifySignature(Request $request, $id)
    {
        $request->validate([
            'signature' => 'required|string',
        ]);

        $kunjungan = Kunjungan::findOrFail($id);
        $laporan = Laporan::where('id_kunjungan', $id)->firstOrFail();

        BuktiPenyelesaian::updateOrCreate(
            ['id_laporan' => $laporan->id_laporan],
            [
                'tanda_tangan_customer' => $request->signature,
                'tanggal_tanda_tangan' => now(),
                'status' => 'Ditandatangani',
            ]
        );

        $kunjungan->update(['status' => 'Selesai']);

        return redirect()->route('kunjungan.show', $id)->with('success', 'Kunjungan kerja selesai secara resmi dan dokumen telah ditandatangani!');
    }

    // 11. Reschedule
    public function reschedule(Request $request, $id)
    {
        $kunjungan = Kunjungan::findOrFail($id);

        $request->validate([
            'alasan_reschedule' => 'required|string|max:255',
        ]);

        $kunjungan->update([
            'status' => 'Reschedule',
            'alasan_reschedule' => $request->alasan_reschedule,
        ]);

        return redirect()->back()->with('success', 'Jadwal berhasil ditolak dan dikembalikan ke Pimpinan untuk dijadwalkan ulang.');
    }

    // 12. Konfirmasi / Terima Jadwal Kunjungan oleh Engineer
    public function terima($id)
    {
        $kunjungan = Kunjungan::findOrFail($id);

        $kunjungan->update([
            'status' => 'Dikonfirmasi',
        ]);

        return redirect()->back()->with('success', 'Jadwal kunjungan berhasil dikonfirmasi dan diterima!');
    }
}