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
use App\Models\KunjunganKonfirmasi;
use App\Models\PeminjamanTool;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class KunjunganController extends Controller
{
    /**
     * Cari kunjungan berdasarkan NOMOR (bukan id angka),
     * karena URL memakai nomor kunjungan, misal: /kunjungan/vmsmit26001
     */
    private function cariKunjungan(string $nomor)
    {
        return Kunjungan::where('nomor', $nomor)->firstOrFail();
    }

    // Sinkronkan daftar konfirmasi: lead + support dapat status 'menunggu'
    // Engineer yang sudah ada tidak di-reset (kecuali diganti)
    private function syncKonfirmasi(Kunjungan $kunjungan, array $supportIds = [])
    {
        $engineerIds = array_unique(array_merge([$kunjungan->id_engineer], $supportIds));
        foreach ($engineerIds as $eid) {
            if (!$eid) continue;
            KunjunganKonfirmasi::firstOrCreate(
                ['id_kunjungan' => $kunjungan->id_kunjungan, 'id_engineer' => $eid],
                ['status' => 'menunggu']
            );
        }
        // Hapus konfirmasi engineer yang sudah tidak terlibat
        KunjunganKonfirmasi::where('id_kunjungan', $kunjungan->id_kunjungan)
            ->whereNotIn('id_engineer', $engineerIds)
            ->delete();
    }

    // Cek peran engineer yang login terhadap kunjungan: 'lead', 'support', atau null
    private function peranEngineer(Kunjungan $kunjungan)
    {
        $user = Auth::user();
        if ($user->id_role != 3) return null;
        $engineer = Engineer::where('id_pengguna', $user->id_pengguna)->first();
        if (!$engineer) return null;
        if ($kunjungan->id_engineer == $engineer->id_engineer) return 'lead';
        if ($kunjungan->supportEngineers()->where('engineers.id_engineer', $engineer->id_engineer)->exists()) return 'support';
        return null;
    }

    // Pastikan hanya lead engineer yang boleh melakukan aksi ini

    // 1. Tampilkan List Kunjungan
    public function index(Request $request)
    {
        $user = Auth::user();
        // OPTIMASI: Tambahkan 'site' di eager loading
        $query = Kunjungan::with(['customer', 'site', 'engineer.user', 'tools', 'supportEngineers.user']);

        // Jika engineer, filter hanya kunjungan miliknya (sebagai lead ATAU support)
        if ($user->id_role == 3) {
            $engineer = Engineer::where('id_pengguna', $user->id_pengguna)->first();
            if ($engineer) {
                $query->where(function($q) use ($engineer) {
                    $q->where('id_engineer', $engineer->id_engineer)
                      ->orWhereHas('supportEngineers', function($sq) use ($engineer) {
                          $sq->where('engineers.id_engineer', $engineer->id_engineer);
                      });
                });
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

    /**
     * Alamat kunjungan SELALU mengikuti data master: alamat site jika dipilih,
     * jika tidak maka alamat customer. Tidak bisa diubah manual dari form.
     */
    private function resolveLokasi($idSite, $idCustomer)
    {
        if ($idSite) {
            $site = \App\Models\CustomerSite::find($idSite);
            if ($site && trim((string) $site->alamat_lengkap) !== '') {
                return $site->alamat_lengkap;
            }
        }
        $customer = \App\Models\Customer::find($idCustomer);
        return $customer->alamat ?? '';
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
            'patokan' => 'nullable|string|max:500',
            'pekerjaan' => 'required|string|max:150',
            'tools' => 'nullable|array',
            'tools.*' => 'exists:tools,id_tool',
            'support_engineers' => 'nullable|array|max:4',
            'support_engineers.*' => 'exists:engineers,id_engineer',
        ]);

        // Alamat dikunci: selalu sinkron dari site/customer yang dipilih
        $lokasi = $this->resolveLokasi($request->id_site, $request->id_customer);

        DB::transaction(function () use ($request, $lokasi) {
            // Nomor kunjungan berurutan dari Format Nomor (prefix) yang bisa diatur di Master Data
            $nomorKunjungan = \App\Models\FormatNomor::generate('kunjungan');

            $kunjungan = Kunjungan::create([
                'nomor' => $nomorKunjungan,
                'id_customer' => $request->id_customer,
                'id_site' => $request->id_site,
                'id_engineer' => $request->id_engineer,
                'tanggal' => $request->tanggal,
                'waktu' => $request->waktu,
                'lokasi' => $lokasi,
                'patokan' => $request->patokan,
                'pekerjaan' => $request->pekerjaan,
                'status' => 'Terjadwal',
            ]);

            if ($request->filled('tools')) {
                foreach ($request->tools as $toolId) {
                    $tool = Tool::lockForUpdate()->find($toolId);
                    if (!$tool || $tool->stok < 1) {
                        throw new \Exception("Stok " . ($tool->nama_alat ?? 'tool') . " tidak mencukupi (tersisa " . ($tool->stok ?? 0) . ").");
                    }
                    $kunjungan->tools()->attach($toolId, ['jumlah' => 1]);
                    $tool->decrement('stok', 1);
                    PeminjamanTool::create([
                        'id_tool' => $toolId,
                        'id_engineer' => $request->id_engineer,
                        'id_kunjungan' => $kunjungan->id_kunjungan,
                        'jumlah' => 1,
                        'tanggal_pinjam' => now(),
                        'status' => 'Dipinjam',
                        'keterangan' => 'Dipakai untuk kunjungan ' . $nomorKunjungan,
                    ]);
                }
            }

            if ($request->filled('support_engineers')) {
                $kunjungan->supportEngineers()->attach($request->support_engineers);
            }
            // Buat daftar konfirmasi untuk lead + support
            $this->syncKonfirmasi($kunjungan, $request->support_engineers ?? []);
        });

        return redirect()->back()->with('success', 'Jadwal Kunjungan berhasil dibuat!');
    }

    // 3. Update Kunjungan
    public function update(Request $request, $id)
    {
        $kunjungan = $this->cariKunjungan($id);

        $request->validate([
            'id_customer' => 'required|exists:customers,id_customer',
            'id_site' => 'nullable|exists:customer_sites,id_site',
            'id_engineer' => 'nullable|exists:engineers,id_engineer',
            'tanggal' => 'required|date',
            'waktu' => 'required',
            'patokan' => 'nullable|string|max:500',
            'pekerjaan' => 'required|string|max:150',
            'tools' => 'nullable|array',
            'tools.*' => 'exists:tools,id_tool',
            'support_engineers' => 'nullable|array|max:4',
            'support_engineers.*' => 'exists:engineers,id_engineer',
        ]);

        // Alamat dikunci: selalu sinkron dari site/customer yang dipilih
        $lokasi = $this->resolveLokasi($request->id_site, $request->id_customer);

        DB::transaction(function () use ($request, $kunjungan, $lokasi) {
            $statusBaru = $kunjungan->status == 'Reschedule' ? 'Terjadwal' : $kunjungan->status;
            $alasan = $kunjungan->status == 'Reschedule' ? null : $kunjungan->alasan_reschedule;

            $kunjungan->update([
                'id_customer' => $request->id_customer,
                'id_site' => $request->id_site,
                'id_engineer' => $request->id_engineer,
                'tanggal' => $request->tanggal,
                'waktu' => $request->waktu,
                'lokasi' => $lokasi,
                'patokan' => $request->patokan,
                'pekerjaan' => $request->pekerjaan,
                'status' => $statusBaru,
                'alasan_reschedule' => $alasan,
            ]);

            $toolBaru = $request->filled('tools') ? $request->tools : [];
            $this->sinkronToolsDenganStok($kunjungan, $toolBaru, $request->id_engineer);

            if ($request->filled('support_engineers')) {
                $kunjungan->supportEngineers()->sync($request->support_engineers);
            } else {
                $kunjungan->supportEngineers()->detach();
            }
            // Sinkronkan daftar konfirmasi
            $this->syncKonfirmasi($kunjungan->fresh(), $request->support_engineers ?? []);
        });

        return redirect()->back()->with('success', 'Data Kunjungan berhasil diperbarui!');
    }

    // 4. Hapus Kunjungan
    public function destroy($id)
    {
        $kunjungan = $this->cariKunjungan($id);

        DB::transaction(function () use ($kunjungan) {
            // Kembalikan semua tools yang masih dipinjam untuk kunjungan ini
            $this->sinkronToolsDenganStok($kunjungan, [], $kunjungan->id_engineer);
            $kunjungan->delete();
        });

        return redirect()->back()->with('success', 'Jadwal Kunjungan berhasil dihapus secara permanen!');
    }

    /**
     * Sinkron tools kunjungan sekaligus mengatur stok & riwayat peminjaman.
     * - Tool yang dilepas  -> peminjaman ditandai Dikembalikan, stok bertambah.
     * - Tool yang ditambah -> validasi stok, peminjaman baru, stok berkurang.
     */
    private function sinkronToolsDenganStok(Kunjungan $kunjungan, array $toolBaru, $idEngineer)
    {
        $toolLama = $kunjungan->tools()->pluck('tools.id_tool')->map(fn($v) => (int) $v)->toArray();
        $toolBaru = array_map('intval', $toolBaru);

        $dilepas = array_diff($toolLama, $toolBaru);
        $ditambah = array_diff($toolBaru, $toolLama);

        // Validasi stok dulu untuk semua tool yang ditambah
        foreach ($ditambah as $toolId) {
            $tool = Tool::lockForUpdate()->find($toolId);
            if (!$tool || $tool->stok < 1) {
                throw new \Exception("Stok " . ($tool->nama_alat ?? 'tool') . " tidak mencukupi (tersisa " . ($tool->stok ?? 0) . ").");
            }
        }

        $syncData = [];
        foreach ($toolBaru as $toolId) {
            $syncData[$toolId] = ['jumlah' => 1];
        }
        $kunjungan->tools()->sync($syncData);

        // Tool dilepas -> kembalikan stok
        foreach ($dilepas as $toolId) {
            $pinjam = PeminjamanTool::where('id_kunjungan', $kunjungan->id_kunjungan)
                ->where('id_tool', $toolId)
                ->where('status', 'Dipinjam')
                ->first();
            if ($pinjam) {
                $pinjam->update(['status' => 'Dikembalikan', 'tanggal_kembali' => now()]);
            }
            Tool::where('id_tool', $toolId)->increment('stok', 1);
        }

        // Tool ditambah -> kurangi stok + catat peminjaman
        foreach ($ditambah as $toolId) {
            Tool::where('id_tool', $toolId)->decrement('stok', 1);
            PeminjamanTool::create([
                'id_tool' => $toolId,
                'id_engineer' => $idEngineer,
                'id_kunjungan' => $kunjungan->id_kunjungan,
                'jumlah' => 1,
                'tanggal_pinjam' => now(),
                'status' => 'Dipinjam',
                'keterangan' => 'Dipakai untuk kunjungan ' . $kunjungan->nomor,
            ]);
        }
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
            'aktivitas.engineer.user', 
            'dokumentasi', 
            'laporan.buktiPenyelesaian',
            'laporan.pembuat.user',
            'pengeluaran',
            'supportEngineers.user',
            'konfirmasi.engineer.user'
        ])->where('nomor', $id)->firstOrFail();

        // Otorisasi: engineer hanya boleh lihat kunjungannya sendiri (lead/support)
        $user = Auth::user();
        if ($user->id_role == 3 && !$this->peranEngineer($kunjungan)) {
            abort(403, 'Anda tidak terlibat dalam kunjungan ini.');
        }

        return view('kunjungan.show', compact('kunjungan'));
    }

    // 6. Engineer Check-in
    public function checkIn(Request $request, $id)
    {
        $kunjungan = Kunjungan::with('customer')->where('nomor', $id)->firstOrFail();

        // Lead & support boleh check-in, tapi harus terlibat di kunjungan ini
        $user = Auth::user();
        if ($user->id_role == 3 && !$this->peranEngineer($kunjungan)) {
            abort(403, 'Anda tidak terlibat dalam kunjungan ini.');
        }

        if (!in_array($kunjungan->status, ['Terjadwal', 'Dikonfirmasi', 'Dikerjakan'])) {
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

        // Cek apakah engineer ini sudah check-in
        $engineer = Engineer::where('id_pengguna', $user->id_pengguna)->first();
        $idEngineer = $engineer ? $engineer->id_engineer : null;
        if ($idEngineer) {
            $sudahCheckin = AktivitasPekerjaan::where('id_kunjungan', $kunjungan->id_kunjungan)
                ->where('id_engineer', $idEngineer)
                ->whereNotNull('waktu_mulai')
                ->exists();
            if ($sudahCheckin) {
                return redirect()->back()->with('error', 'Anda sudah check-in untuk kunjungan ini.');
            }
        }

        $kunjungan->update([
            'status' => 'Dikerjakan',
            'check_in_latitude' => $lat,
            'check_in_longitude' => $lng
        ]);

        AktivitasPekerjaan::create([
            'id_kunjungan' => $kunjungan->id_kunjungan,
            'id_engineer' => $idEngineer,
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
            'foto' => 'required|file|mimes:jpeg,png,jpg,mp4,mov,3gp,webm|max:51200',
            'keterangan' => 'nullable|string',
        ]);

        $kunjungan = $this->cariKunjungan($id);

        $file = $request->file('foto');
        $filename = time() . '_' . $file->getClientOriginalName();
        $file->move(public_path('uploads/dokumentasi'), $filename);

        Dokumentasi::create([
            'id_kunjungan' => $kunjungan->id_kunjungan,
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
            'bukti_nota' => 'nullable|file|mimes:jpeg,png,jpg,mp4,mov,3gp,webm|max:51200',
        ]);

        $path = null;
        if ($request->hasFile('bukti_nota')) {
            $file = $request->file('bukti_nota');
            $filename = time() . '_nota_' . $file->getClientOriginalName();
            $file->move(public_path('uploads/pengeluaran'), $filename);
            $path = 'uploads/pengeluaran/' . $filename;
        }

        $kunjungan = $this->cariKunjungan($id);

        Pengeluaran::create([
            'id_kunjungan' => $kunjungan->id_kunjungan,
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
        $kunjungan = $this->cariKunjungan($id);

        // Harus terlibat di kunjungan ini
        $user = Auth::user();
        if ($user->id_role == 3 && !$this->peranEngineer($kunjungan)) {
            abort(403, 'Anda tidak terlibat dalam kunjungan ini.');
        }

        $request->validate([
            'catatan' => 'required|string',
            'lokasi_gps' => 'required|string',
        ]);

        $coords = explode(',', str_replace(' ', '', $request->lokasi_gps));
        $lat = $coords[0] ?? null;
        $lng = $coords[1] ?? null;

        // Check-out per engineer: update aktivitas miliknya sendiri
        $engineer = Engineer::where('id_pengguna', $user->id_pengguna)->first();
        $idEngineer = $engineer ? $engineer->id_engineer : null;

        $aktivitas = AktivitasPekerjaan::where('id_kunjungan', $kunjungan->id_kunjungan)
            ->where('id_engineer', $idEngineer)
            ->whereNotNull('waktu_mulai')
            ->whereNull('waktu_selesai')
            ->latest()
            ->first();

        if (!$aktivitas) {
            return redirect()->back()->with('error', 'Anda belum check-in atau sudah check-out untuk kunjungan ini.');
        }

        $aktivitas->update([
            'waktu_selesai' => now(),
            'catatan' => $request->catatan,
            'lokasi_checkout' => $request->lokasi_gps,
        ]);

        // Update koordinat check-out kunjungan (terakhir yang check-out)
        $kunjungan->update([
            'check_out_latitude' => $lat,
            'check_out_longitude' => $lng
        ]);

        return redirect()->back()->with('success', 'Check-out berhasil! Silakan buat laporan jika belum ada.');
    }

    // 9a. Buat Laporan (hanya 1x per kunjungan - siapa cepat dia dapat)
    public function buatLaporan($id)
    {
        $kunjungan = $this->cariKunjungan($id);

        $user = Auth::user();
        if ($user->id_role == 3 && !$this->peranEngineer($kunjungan)) {
            abort(403, 'Anda tidak terlibat dalam kunjungan ini.');
        }

        // Jika sudah ada laporan, tolak
        if ($kunjungan->laporan) {
            return redirect()->back()->with('error', 'Laporan sudah dibuat oleh engineer lain. Hanya 1 laporan per kunjungan.');
        }

        // Harus sudah check-out dulu
        $engineer = Engineer::where('id_pengguna', $user->id_pengguna)->first();
        $idEngineer = $engineer ? $engineer->id_engineer : null;
        $sudahCheckout = AktivitasPekerjaan::where('id_kunjungan', $kunjungan->id_kunjungan)
            ->where('id_engineer', $idEngineer)
            ->whereNotNull('waktu_selesai')
            ->exists();
        if ($user->id_role == 3 && !$sudahCheckout) {
            return redirect()->back()->with('error', 'Anda harus check-out dulu sebelum membuat laporan.');
        }

        $laporan = Laporan::firstOrCreate(
            ['id_kunjungan' => $kunjungan->id_kunjungan],
            [
                'tanggal_dibuat' => now(),
                'status_laporan' => 'Terbuat Otomatis'
            ]
        );

        // Catat siapa yang membuat
        if ($idEngineer && !$laporan->id_engineer_pembuat) {
            $laporan->update(['id_engineer_pembuat' => $idEngineer]);
        }

        return redirect()->back()->with('success', 'Laporan berhasil dibuat! Menunggu verifikasi tanda tangan customer.');
    }

    // 9b. Revisi Catatan Pekerjaan (sebelum laporan dikunci TTD customer)
    public function revisiCatatan(Request $request, $id)
    {
        $kunjungan = Kunjungan::with('laporan.buktiPenyelesaian')->where('nomor', $id)->firstOrFail();

        if (!$kunjungan->laporan || $kunjungan->laporan->buktiPenyelesaian) {
            return redirect()->back()->with('error', 'Catatan tidak dapat direvisi karena laporan sudah dikunci tanda tangan.');
        }

        $request->validate([
            'catatan' => 'required|string',
        ]);

        $aktivitas = AktivitasPekerjaan::where('id_kunjungan', $kunjungan->id_kunjungan)->latest()->first();
        if ($aktivitas) {
            $aktivitas->update(['catatan' => $request->catatan]);
        }

        return redirect()->back()->with('success', 'Catatan berhasil direvisi dan akan tampil di laporan PDF.');
    }

    // 10. Tanda Tangan Customer & Engineer
    public function verifySignature(Request $request, $id)
    {
        $request->validate([
            'signature' => 'required|string',
            'signature_engineer' => 'required|string',
        ]);

        $kunjungan = $this->cariKunjungan($id);
        $laporan = Laporan::where('id_kunjungan', $kunjungan->id_kunjungan)->firstOrFail();

        BuktiPenyelesaian::updateOrCreate(
            ['id_laporan' => $laporan->id_laporan],
            [
                'tanda_tangan_customer' => $request->signature,
                'tanda_tangan_engineer' => $request->signature_engineer,
                'tanggal_tanda_tangan' => now(),
                'status' => 'Ditandatangani',
            ]
        );

        // Kunjungan selesai -> tools TIDAK otomatis kembali.
        // Engineer wajib mengembalikan manual via halaman Pengembalian agar stok realtime
        // (stok hanya bertambah saat tools benar-benar sudah kembali fisik).

        $kunjungan->update(['status' => 'Selesai', 'draft_ttd_customer' => null, 'draft_ttd_engineer' => null]);

        return redirect()->route('kunjungan.show', $id)->with('success', 'Kunjungan kerja selesai secara resmi dan dokumen telah ditandatangani customer & engineer!');
    }

    // 10b. Simpan draft TTD otomatis (dipanggil via AJAX saat pad dikunci/diisi),
    // agar tanda tangan tidak hilang jika halaman di-refresh sebelum submit final.
    public function saveSignatureDraft(Request $request, $id)
    {
        $request->validate([
            'type' => 'required|in:customer,engineer',
            'signature' => 'nullable|string|max:2000000',
        ]);

        $kunjungan = $this->cariKunjungan($id);

        // Jangan terima draft jika laporan sudah dikunci final
        if ($kunjungan->laporan && $kunjungan->laporan->buktiPenyelesaian) {
            return response()->json(['ok' => false, 'message' => 'Laporan sudah dikunci final.'], 400);
        }

        $field = $request->type === 'customer' ? 'draft_ttd_customer' : 'draft_ttd_engineer';
        $kunjungan->update([$field => $request->signature]);

        return response()->json(['ok' => true]);
    }

    // 11. Reschedule / Tolak Jadwal oleh Engineer
    // Saat ditolak: semua tools kunjungan ikut dilepas (stok kembali, peminjaman ditutup)
    public function reschedule(Request $request, $id)
    {
        $kunjungan = $this->cariKunjungan($id);

        $request->validate([
            'alasan_reschedule' => 'required|string|max:255',
        ]);

        $user = Auth::user();
        if ($user->id_role == 3) {
            // Penolakan per engineer: catat siapa yang menolak + alasannya
            $engineer = Engineer::where('id_pengguna', $user->id_pengguna)->first();
            if (!$engineer || !$this->peranEngineer($kunjungan)) {
                abort(403, 'Anda tidak terlibat dalam kunjungan ini.');
            }
            KunjunganKonfirmasi::updateOrCreate(
                ['id_kunjungan' => $kunjungan->id_kunjungan, 'id_engineer' => $engineer->id_engineer],
                ['status' => 'ditolak', 'alasan_ditolak' => $request->alasan_reschedule, 'waktu_konfirmasi' => now()]
            );
            return redirect()->back()->with('success', 'Penolakan tercatat. Pimpinan akan mengganti Anda dengan engineer lain.');
        }

        // Pimpinan: tolak/reschedule seluruh kunjungan (logic lama - lepas tools)
        $toolIds = $kunjungan->tools()->pluck('tools.id_tool')->toArray();
        foreach ($toolIds as $toolId) {
            $pinjam = PeminjamanTool::where('id_kunjungan', $kunjungan->id_kunjungan)
                ->where('id_tool', $toolId)
                ->where('status', 'Dipinjam')
                ->first();
            if ($pinjam) {
                $pinjam->update(['status' => 'Dibatalkan', 'tanggal_kembali' => now(), 'keterangan' => 'Dibatalkan: kunjungan ditolak engineer sebelum tools dibawa.']);
            }
            Tool::where('id_tool', $toolId)->increment('stok', 1);
        }
        $kunjungan->tools()->detach();

        $kunjungan->update([
            'status' => 'Reschedule',
            'alasan_reschedule' => $request->alasan_reschedule,
        ]);

        return redirect()->back()->with('success', 'Jadwal berhasil ditolak, tools kunjungan dikembalikan ke stok, dan dikembalikan ke Pimpinan untuk dijadwalkan ulang.');
    }

    // 12. Konfirmasi / Terima Jadwal Kunjungan oleh Engineer (per engineer)
    public function terima($id)
    {
        $kunjungan = $this->cariKunjungan($id);

        $user = Auth::user();
        if ($user->id_role == 3) {
            $engineer = Engineer::where('id_pengguna', $user->id_pengguna)->first();
            if (!$engineer || !$this->peranEngineer($kunjungan)) {
                abort(403, 'Anda tidak terlibat dalam kunjungan ini.');
            }
            KunjunganKonfirmasi::updateOrCreate(
                ['id_kunjungan' => $kunjungan->id_kunjungan, 'id_engineer' => $engineer->id_engineer],
                ['status' => 'diterima', 'alasan_ditolak' => null, 'waktu_konfirmasi' => now()]
            );

            // Jika SEMUA engineer sudah terima -> kunjungan Dikonfirmasi
            $total = KunjunganKonfirmasi::where('id_kunjungan', $kunjungan->id_kunjungan)->count();
            $diterima = KunjunganKonfirmasi::where('id_kunjungan', $kunjungan->id_kunjungan)->where('status', 'diterima')->count();
            if ($total > 0 && $total == $diterima && $kunjungan->status == 'Terjadwal') {
                $kunjungan->update(['status' => 'Dikonfirmasi']);
            }

            return redirect()->back()->with('success', 'Konfirmasi diterima! Menunggu konfirmasi engineer lainnya.');
        }

        $kunjungan->update(['status' => 'Dikonfirmasi']);
        return redirect()->back()->with('success', 'Jadwal kunjungan berhasil dikonfirmasi dan diterima!');
    }

    // 12b. Pimpinan ganti engineer yang menolak dengan engineer lain
    public function gantiEngineer(Request $request, $id)
    {
        $kunjungan = $this->cariKunjungan($id);

        $request->validate([
            'id_engineer_lama' => 'required|exists:engineers,id_engineer',
            'id_engineer_baru' => 'required|exists:engineers,id_engineer|different:id_engineer_lama',
        ]);

        $lama = $request->id_engineer_lama;
        $baru = $request->id_engineer_baru;

        DB::transaction(function () use ($kunjungan, $lama, $baru) {
            // Jika yang diganti adalah lead, update id_engineer kunjungan
            if ($kunjungan->id_engineer == $lama) {
                $kunjungan->update(['id_engineer' => $baru]);
            } else {
                // Jika support, ganti di pivot
                $kunjungan->supportEngineers()->detach($lama);
                $kunjungan->supportEngineers()->attach($baru);
            }
            // Hapus konfirmasi lama, buat baru status menunggu
            KunjunganKonfirmasi::where('id_kunjungan', $kunjungan->id_kunjungan)
                ->where('id_engineer', $lama)->delete();
            KunjunganKonfirmasi::firstOrCreate(
                ['id_kunjungan' => $kunjungan->id_kunjungan, 'id_engineer' => $baru],
                ['status' => 'menunggu']
            );
        });

        $namaBaru = Engineer::with('user')->find($baru);
        return redirect()->back()->with('success', 'Engineer berhasil diganti dengan ' . ($namaBaru->user->nama ?? 'engineer baru') . '. Menunggu konfirmasi darinya.');
    }
}