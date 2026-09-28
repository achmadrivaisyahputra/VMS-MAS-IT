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
                // Tools hanya direncanakan dulu (attach); stok berkurang & tercatat
                // per engineer SETELAH seluruh engineer setuju (lihat aktifkanToolsKunjungan)
                $syncData = [];
                foreach ($request->tools as $toolId) {
                    $syncData[$toolId] = ['jumlah' => 1];
                }
                $kunjungan->tools()->sync($syncData);
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
            $this->sinkronToolsKunjungan($kunjungan, $toolBaru);

            if ($request->filled('support_engineers')) {
                $kunjungan->supportEngineers()->sync($request->support_engineers);
            } else {
                $kunjungan->supportEngineers()->detach();
            }
            // Jika tools sudah aktif & tim berubah, pastikan engineer baru kebagian catatan
            $kunjungan->refresh();
            if ($this->toolsSudahAktif($kunjungan)) {
                $this->aktifkanToolsKunjungan($kunjungan);
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
            $this->sinkronToolsKunjungan($kunjungan, []);
            $kunjungan->delete();
        });

        return redirect()->back()->with('success', 'Jadwal Kunjungan berhasil dihapus secara permanen!');
    }

    /**
     * Sinkron tools kunjungan sekaligus mengatur stok & riwayat peminjaman.
     * - Tool yang dilepas  -> peminjaman ditandai Dikembalikan, stok bertambah.
     * - Tool yang ditambah -> validasi stok, peminjaman baru, stok berkurang.
     */
    /**
     * Sinkron tools kunjungan (daftar rencana tools).
     * - Jika tools kunjungan BELUM diaktifkan (belum semua engineer setuju):
     *   hanya sync attach, tanpa ubah stok / catatan peminjaman.
     * - Jika SUDAH diaktifkan: tool dilepas -> batalkan catatan + kembalikan stok;
     *   tool ditambah -> aktifkan langsung (stok + catatan per engineer).
     */
    private function sinkronToolsKunjungan(Kunjungan $kunjungan, array $toolBaru)
    {
        $toolLama = $kunjungan->tools()->pluck('tools.id_tool')->map(fn($v) => (int) $v)->toArray();
        $toolBaru = array_map('intval', $toolBaru);

        $dilepas = array_diff($toolLama, $toolBaru);

        $syncData = [];
        foreach ($toolBaru as $toolId) {
            $syncData[$toolId] = ['jumlah' => 1];
        }
        $kunjungan->tools()->sync($syncData);

        $sudahAktif = $this->toolsSudahAktif($kunjungan);

        // Tool dilepas -> batalkan catatan semua engineer + kembalikan stok (1x)
        foreach ($dilepas as $toolId) {
            if ($sudahAktif) {
                PeminjamanTool::where('id_kunjungan', $kunjungan->id_kunjungan)
                    ->where('id_tool', $toolId)
                    ->where('status', 'Dipinjam')
                    ->update([
                        'status' => 'Dibatalkan',
                        'tanggal_kembali' => now(),
                        'keterangan' => 'Dibatalkan: tool dilepas dari kunjungan ' . $kunjungan->nomor,
                    ]);
                Tool::where('id_tool', $toolId)->increment('stok', 1);
            }
        }

        // Tool ditambah setelah aktif -> langsung aktifkan
        if ($sudahAktif) {
            $this->aktifkanToolsKunjungan($kunjungan);
        }
    }

    /**
     * Apakah tools kunjungan sudah diaktifkan (stok sudah berkurang)?
     */
    private function toolsSudahAktif(Kunjungan $kunjungan)
    {
        return PeminjamanTool::where('id_kunjungan', $kunjungan->id_kunjungan)
            ->where('status', 'Dipinjam')
            ->exists();
    }

    /**
     * Aktifkan tools kunjungan: kurangi stok 1x per tool, buat catatan
     * peminjaman untuk MASING-MASING engineer (lead + support).
     * Idempotent: aman dipanggil berulang.
     */
    private function aktifkanToolsKunjungan(Kunjungan $kunjungan)
    {
        $engineers = collect([$kunjungan->id_engineer])
            ->merge($kunjungan->supportEngineers()->pluck('engineers.id_engineer'))
            ->filter()->unique()->values();

        $kunjungan->load('tools');

        foreach ($kunjungan->tools as $tool) {
            $sudahAktif = PeminjamanTool::where('id_kunjungan', $kunjungan->id_kunjungan)
                ->where('id_tool', $tool->id_tool)
                ->where('status', 'Dipinjam')
                ->exists();

            if (!$sudahAktif) {
                $toolModel = Tool::lockForUpdate()->find($tool->id_tool);
                if (!$toolModel || $toolModel->stok < 1) {
                    throw new \Exception("Stok " . ($toolModel->nama_alat ?? 'tool') . " tidak mencukupi (tersisa " . ($toolModel->stok ?? 0) . ").");
                }
                $toolModel->decrement('stok', 1);
            }

            // Catatan per engineer (termasuk engineer yang baru ditambahkan)
            foreach ($engineers as $idEng) {
                PeminjamanTool::firstOrCreate(
                    ['id_kunjungan' => $kunjungan->id_kunjungan, 'id_tool' => $tool->id_tool, 'id_engineer' => $idEng, 'status' => 'Dipinjam'],
                    ['jumlah' => 1, 'tanggal_pinjam' => now(), 'keterangan' => 'Dipakai untuk kunjungan ' . $kunjungan->nomor]
                );
            }
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

        // Alur baru: check-out hanya boleh setelah laporan dibuat & TTD terkunci
        if (!$kunjungan->laporan || !$kunjungan->laporan->buktiPenyelesaian) {
            return redirect()->back()->with('error', 'Buat laporan dan selesaikan tanda tangan dulu sebelum check-out.');
        }

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

        // Jika SEMUA engineer (lead + support) sudah check-out -> kunjungan Selesai
        // Tools TIDAK otomatis kembali; engineer wajib mengembalikan manual via halaman Pengembalian
        $allEngineerIds = array_unique(array_merge(
            [$kunjungan->id_engineer],
            $kunjungan->supportEngineers()->pluck('engineers.id_engineer')->toArray()
        ));
        $sudahCheckoutSemua = true;
        foreach ($allEngineerIds as $eid) {
            if (!$eid) continue;
            $co = AktivitasPekerjaan::where('id_kunjungan', $kunjungan->id_kunjungan)
                ->where('id_engineer', $eid)
                ->whereNotNull('waktu_selesai')
                ->exists();
            if (!$co) { $sudahCheckoutSemua = false; break; }
        }
        if ($sudahCheckoutSemua) {
            $kunjungan->update(['status' => 'Selesai']);
            return redirect()->back()->with('success', 'Check-out berhasil! Semua engineer sudah check-out, kunjungan selesai.');
        }

        return redirect()->back()->with('success', 'Check-out berhasil! Menunggu engineer lain check-out.');
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

        // Harus sudah check-in dulu (laporan dibuat sebelum check-out)
        $engineer = Engineer::where('id_pengguna', $user->id_pengguna)->first();
        $idEngineer = $engineer ? $engineer->id_engineer : null;
        $sudahCheckin = AktivitasPekerjaan::where('id_kunjungan', $kunjungan->id_kunjungan)
            ->where('id_engineer', $idEngineer)
            ->whereNotNull('waktu_mulai')
            ->exists();
        if ($user->id_role == 3 && !$sudahCheckin) {
            return redirect()->back()->with('error', 'Anda harus check-in dulu sebelum membuat laporan.');
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
            'hasil_pekerjaan' => 'required|string',
            'catatan_tambahan' => 'nullable|string',
        ]);

        $kunjungan->laporan->update([
            'hasil_pekerjaan' => $request->hasil_pekerjaan,
            'catatan_tambahan' => $request->catatan_tambahan,
        ]);

        return redirect()->back()->with('success', 'Laporan berhasil direvisi dan PDF otomatis mengikuti revisi terbaru.');
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

        // TTD selesai -> laporan terkunci, tapi kunjungan BELUM selesai.
        // Engineer masih harus check-out; status Selesai saat semua sudah check-out.
        $kunjungan->update(['draft_ttd_customer' => null, 'draft_ttd_engineer' => null]);

        return redirect()->route('kunjungan.show', $id)->with('success', 'Tanda tangan tersimpan dan terkunci! Silakan check-out untuk menyelesaikan kunjungan.');
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
        // Batalkan SEMUA catatan peminjaman (tiap engineer) + kembalikan stok 1x per tool
        $toolIds = $kunjungan->tools()->pluck('tools.id_tool')->toArray();
        foreach ($toolIds as $toolId) {
            $dibatalkan = PeminjamanTool::where('id_kunjungan', $kunjungan->id_kunjungan)
                ->where('id_tool', $toolId)
                ->where('status', 'Dipinjam')
                ->update(['status' => 'Dibatalkan', 'tanggal_kembali' => now(), 'keterangan' => 'Dibatalkan: kunjungan ditolak sebelum tools dibawa.']);
            if ($dibatalkan) {
                Tool::where('id_tool', $toolId)->increment('stok', 1);
            }
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
                // Semua setuju -> stok tools berkurang & tercatat di tiap engineer
                $this->aktifkanToolsKunjungan($kunjungan->fresh());
            }

            return redirect()->back()->with('success', 'Konfirmasi diterima! Menunggu konfirmasi engineer lainnya.');
        }

        $kunjungan->update(['status' => 'Dikonfirmasi']);
        // Pimpinan konfirmasi langsung -> tools ikut diaktifkan
        $this->aktifkanToolsKunjungan($kunjungan->fresh());
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
            // Pengganti OTOMATIS mewarisi role yang menolak:
            // jika yang menolak lead -> pengganti jadi lead; jika support -> jadi support
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
            // Tools kunjungan ikut pindah ke engineer pengganti
            $sudahPunya = PeminjamanTool::where('id_kunjungan', $kunjungan->id_kunjungan)
                ->where('id_engineer', $baru)
                ->where('status', 'Dipinjam')
                ->exists();
            if ($sudahPunya) {
                // Pengganti sudah punya catatan (misal tadinya support) -> hapus catatan lama
                PeminjamanTool::where('id_kunjungan', $kunjungan->id_kunjungan)
                    ->where('id_engineer', $lama)
                    ->where('status', 'Dipinjam')
                    ->delete();
            } else {
                PeminjamanTool::where('id_kunjungan', $kunjungan->id_kunjungan)
                    ->where('id_engineer', $lama)
                    ->where('status', 'Dipinjam')
                    ->update(['id_engineer' => $baru]);
            }
        });

        $namaBaru = Engineer::with('user')->find($baru);
        $roleDiganti = $kunjungan->id_engineer == $baru ? 'LEAD' : 'SUPPORT';
        return redirect()->back()->with('success', 'Engineer berhasil diganti dengan ' . ($namaBaru->user->nama ?? 'engineer baru') . ' sebagai ' . $roleDiganti . '. Menunggu konfirmasi darinya.');
    }
}