@extends('layouts.app')

@section('title', 'Detail Kunjungan - ' . $kunjungan->nomor)
@section('header_title', 'Lembar Pelaksanaan Kunjungan')

@section('content')
<div class="space-y-6 max-w-5xl mx-auto">

    @if(session('success'))
        <div class="p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-700 text-xs flex items-center gap-2 font-medium shadow-sm">
            <svg class="w-5 h-5 shrink-0 text-emerald-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
            <span>{{ session('success') }}</span>
        </div>
    @endif
    @if(session('error'))
        <div class="p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-700 text-xs flex items-center gap-2 font-medium shadow-sm">
            <svg class="w-5 h-5 shrink-0 text-rose-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
            <span>{{ session('error') }}</span>
        </div>
    @endif

    <!-- Header Summary Card -->
    <div class="p-5 md:p-6 rounded-2xl bg-gradient-to-r from-[#002266] to-[#0044cc] shadow-lg flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
        <div>
            <div class="flex items-center gap-3">
                <span class="font-mono text-xs md:text-sm font-bold text-white bg-white/20 px-2 py-1 rounded-lg">{{ $kunjungan->nomor }}</span>
                <span class="px-3 py-1 rounded-full text-[11px] font-bold bg-white text-[#002266] shadow-sm">
                    {{ $kunjungan->status }}
                </span>
            </div>
            <h2 class="text-lg md:text-xl font-bold text-white mt-3">{{ $kunjungan->pekerjaan }}</h2>
            <p class="text-xs text-blue-200 mt-1 font-medium">{{ $kunjungan->customer->nama_perusahaan ?? '-' }} • {{ $kunjungan->alamat_sinkron }}</p>
            @if($kunjungan->patokan)
                <p class="text-xs text-amber-200 mt-1 font-medium">📎 Patokan: {{ $kunjungan->patokan }}</p>
            @endif
        </div>
        <div class="flex items-center gap-3">
            @if($kunjungan->status == 'Selesai' || $kunjungan->laporan)
                <button type="button" onclick="bukaPreviewPdf()"
                   class="px-4 py-2.5 bg-white hover:bg-slate-100 text-[#002266] rounded-xl text-xs font-bold flex items-center gap-2 shadow-md transition">
                    <svg class="w-4 h-4 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    <span>Cetak PDF Laporan</span>
                </button>
            @endif
        </div>
    </div>

    <!-- Stepper Status Kunjungan -->
    <div class="p-4 md:p-6 rounded-2xl bg-white border border-slate-200 shadow-sm">
        <h4 class="text-xs font-bold text-slate-500 uppercase tracking-wider mb-3">Status Alur Kunjungan</h4>
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-2 text-center text-xs">
            <div class="p-2.5 rounded-xl font-bold {{ $kunjungan->status == 'Terjadwal' ? 'bg-blue-50 border border-blue-200 text-[#003399]' : 'bg-slate-50 text-slate-400 border border-slate-200' }}">
                1. Terjadwal
            </div>
            <div class="p-2.5 rounded-xl font-bold {{ $kunjungan->status == 'Dikonfirmasi' ? 'bg-blue-50 border border-blue-200 text-[#003399]' : 'bg-slate-50 text-slate-400 border border-slate-200' }}">
                2. Check-in (GPS)
            </div>
            <div class="p-2.5 rounded-xl font-bold {{ ($kunjungan->status == 'Dikerjakan' && !$kunjungan->laporan) ? 'bg-blue-50 border border-blue-200 text-[#003399]' : 'bg-slate-50 text-slate-400 border border-slate-200' }}">
                3. On-Site & Foto
            </div>
            <div class="p-2.5 rounded-xl font-bold {{ ($kunjungan->status == 'Selesai' || ($kunjungan->status == 'Dikerjakan' && $kunjungan->laporan)) ? 'bg-emerald-50 border border-emerald-200 text-emerald-600' : 'bg-slate-50 text-slate-400 border border-slate-200' }}">
                4. TTD Customer
            </div>
        </div>
    </div>

    <!-- MAPS & LOKASI LENGKAP -->
    @php
        $targetLat = $kunjungan->site->latitude ?? ($kunjungan->customer->latitude ?? null);
        $targetLng = $kunjungan->site->longitude ?? ($kunjungan->customer->longitude ?? null);
        $locationQuery = ($targetLat && $targetLng) ? "{$targetLat},{$targetLng}" : urlencode($kunjungan->lokasi);
    @endphp

    <div class="p-5 md:p-6 rounded-2xl bg-white border border-slate-200 shadow-sm space-y-4">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-slate-100 pb-3">
            <div>
                <h4 class="text-sm font-bold text-[#002266] flex items-center gap-2">
                    📍 Peta Lokasi & Koordinat Target
                </h4>
                <p class="text-xs text-slate-500 font-medium mt-0.5">
                    {{ $kunjungan->site->nama_cabang ?? $kunjungan->customer->nama_perusahaan ?? 'Lokasi Tujuan' }} — {{ $kunjungan->alamat_sinkron }}
                    @if($kunjungan->patokan)
                        <span class="text-amber-600 font-semibold">(📎 {{ $kunjungan->patokan }})</span>
                    @endif
                </p>
            </div>
            <div class="flex items-center gap-2">
                <a href="https://www.google.com/maps/search/?api=1&query={{ $locationQuery }}" target="_blank" class="px-3 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-xs font-bold transition flex items-center gap-1.5">
                    🗺️ Buka Google Maps
                </a>
                <a href="https://www.google.com/maps/dir/?api=1&destination={{ $locationQuery }}" target="_blank" class="px-3 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-xs font-bold transition flex items-center gap-1.5 shadow-sm">
                    🧭 Navigasi Rute
                </a>
            </div>
        </div>

        <!-- Iframe Google Maps -->
        <div class="w-full h-64 rounded-xl overflow-hidden border border-slate-200 shadow-inner">
            <iframe 
                class="w-full h-full border-0"
                loading="lazy"
                allowfullscreen
                src="https://maps.google.com/maps?q={{ $locationQuery }}&z=15&output=embed">
            </iframe>
        </div>
    </div>

    <!-- SECTION 1: Konfirmasi Jadwal per Engineer -->
    @if($kunjungan->status == 'Terjadwal')
        @php
            $konfirmasiList = $kunjungan->konfirmasi()->with('engineer.user')->get();
            $myKonfirmasi = null;
            if (Auth::user()->id_role == 3) {
                $engLogin = \App\Models\Engineer::where('id_pengguna', Auth::user()->id_pengguna)->first();
                $myKonfirmasi = $engLogin ? $konfirmasiList->firstWhere('id_engineer', $engLogin->id_engineer) : null;
            }
            $adaDitolak = $konfirmasiList->where('status', 'ditolak')->count() > 0;
        @endphp
        <div class="p-6 rounded-2xl bg-amber-50 border border-amber-200 space-y-4 shadow-sm">
            <div class="flex items-center justify-between">
                <h4 class="text-sm font-bold text-amber-900">⚡ Konfirmasi Penugasan Jadwal</h4>
                <span class="text-xs font-semibold bg-amber-200 text-amber-800 px-2.5 py-0.5 rounded-full">Menunggu Konfirmasi</span>
            </div>

            <!-- Daftar status konfirmasi tiap engineer -->
            <div class="space-y-2">
                @foreach($konfirmasiList as $kf)
                    @php
                        $isLeadKf = $kf->id_engineer == $kunjungan->id_engineer;
                        $namaKf = $kf->engineer->user->nama ?? '-';
                    @endphp
                    <div class="flex items-center justify-between p-3 bg-white rounded-xl border border-amber-100 text-xs">
                        <div>
                            <p class="font-bold text-slate-800">{{ $namaKf }}
                                <span class="ml-1 px-1.5 py-0.5 rounded text-[10px] font-bold {{ $isLeadKf ? 'bg-[#002266] text-white' : 'bg-slate-200 text-slate-600' }}">{{ $isLeadKf ? 'LEAD' : 'SUPPORT' }}</span>
                            </p>
                            @if($kf->status == 'ditolak' && $kf->alasan_ditolak)
                                <p class="text-rose-600 font-medium mt-0.5">Alasan tolak: "{{ $kf->alasan_ditolak }}"</p>
                            @endif
                        </div>
                        <div class="flex items-center gap-2">
                            @if($kf->status == 'diterima')
                                <span class="px-2.5 py-1 rounded-full bg-emerald-100 text-emerald-700 font-bold text-[11px]">✅ Diterima</span>
                            @elseif($kf->status == 'ditolak')
                                <span class="px-2.5 py-1 rounded-full bg-rose-100 text-rose-700 font-bold text-[11px]">❌ Ditolak</span>
                                @if(in_array(Auth::user()->id_role, [1, 2]))
                                    <button type="button" onclick="document.getElementById('ganti-{{ $kf->id_engineer }}').classList.toggle('hidden')" class="px-3 py-1.5 rounded-lg bg-[#003399] hover:bg-[#002266] text-white font-bold text-[11px] transition">🔄 Ganti</button>
                                @endif
                            @else
                                <span class="px-2.5 py-1 rounded-full bg-slate-100 text-slate-500 font-bold text-[11px]">⏳ Menunggu</span>
                            @endif
                        </div>
                    </div>
                    @if($kf->status == 'ditolak' && in_array(Auth::user()->id_role, [1, 2]))
                        <form id="ganti-{{ $kf->id_engineer }}" action="{{ route('kunjungan.ganti-engineer', $kunjungan->nomor) }}" method="POST" class="hidden p-3 bg-white rounded-xl border border-blue-200 space-y-2">
                            @csrf
                            <input type="hidden" name="id_engineer_lama" value="{{ $kf->id_engineer }}">
                            <label class="block text-xs font-bold text-slate-700">Ganti {{ $namaKf }} <span class="px-1.5 py-0.5 rounded text-[10px] font-bold {{ $isLeadKf ? 'bg-[#002266] text-white' : 'bg-slate-200 text-slate-600' }}">{{ $isLeadKf ? 'LEAD' : 'SUPPORT' }}</span> dengan:</label>
                            <p class="text-[11px] text-slate-500">Pengganti otomatis menjadi {{ $isLeadKf ? 'LEAD' : 'SUPPORT' }}.</p>
                            <div class="flex gap-2">
                                <select name="id_engineer_baru" required class="flex-1 px-3 py-2 border border-slate-300 rounded-xl text-xs">
                                    <option value="">-- Pilih Engineer --</option>
                                    @foreach(\App\Models\Engineer::with('user')->get() as $engOpt)
                                        @if(!$konfirmasiList->contains('id_engineer', $engOpt->id_engineer))
                                            <option value="{{ $engOpt->id_engineer }}">{{ $engOpt->user->nama ?? '-' }} ({{ $engOpt->kode ?? '-' }})</option>
                                        @endif
                                    @endforeach
                                </select>
                                <button type="submit" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-bold">Ganti</button>
                            </div>
                        </form>
                    @endif
                @endforeach
            </div>

            @if($adaDitolak && Auth::user()->id_role == 3)
                <p class="text-xs text-rose-600 font-medium text-center">⚠️ Ada engineer yang menolak. Menunggu pimpinan mengganti.</p>
            @endif

            @if(Auth::user()->id_role == 3 && $myKonfirmasi && $myKonfirmasi->status == 'menunggu')
                <p class="text-xs text-amber-800 font-medium">Silakan konfirmasi penerimaan penugasan ini sebelum menuju lokasi kerja.</p>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 pt-2">
                    <!-- Form Terima -->
                    <form action="{{ route('kunjungan.terima', $kunjungan->nomor) }}" method="POST">
                        @csrf
                        <button type="submit" class="w-full py-3 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-bold shadow-md transition flex items-center justify-center gap-2">
                            ✅ Terima & Konfirmasi Jadwal
                        </button>
                    </form>

                    <!-- Form Tolak -->
                    <form action="{{ route('kunjungan.reschedule', $kunjungan->nomor) }}" method="POST" class="space-y-2">
                        @csrf
                        <div class="flex gap-2">
                            <input type="text" name="alasan_reschedule" required placeholder="Alasan Tolak (Contoh: Jadwal Bentrok)" class="w-full px-3 py-2 bg-white border border-slate-300 focus:border-rose-400 focus:ring-rose-400 rounded-xl text-xs text-slate-800">
                            <button type="button" onclick="if(!this.form.checkValidity()) { this.form.reportValidity(); return; } showConfirmModal(this.form, 'Tolak Jadwal', 'Apakah Anda yakin ingin menolak jadwal ini?')" class="px-4 py-2 bg-rose-600 hover:bg-rose-700 text-white rounded-xl text-xs font-bold transition whitespace-nowrap">
                                ❌ Tolak
                            </button>
                        </div>
                    </form>
                </div>
            @elseif(Auth::user()->id_role == 3 && $myKonfirmasi && $myKonfirmasi->status == 'diterima')
                <p class="text-xs text-emerald-700 font-bold text-center">✅ Anda sudah mengkonfirmasi. Menunggu engineer lainnya.</p>
            @endif
        </div>
    @endif
    @if(Auth::user()->id_role == 3)
        @if(in_array($kunjungan->status, ['Dikonfirmasi', 'Dikerjakan']))
            @php
                $sudahCheckinSaya = false;
                if (Auth::user()->id_role == 3) {
                    $engSaya = \App\Models\Engineer::where('id_pengguna', Auth::user()->id_pengguna)->first();
                    if ($engSaya) {
                        $sudahCheckinSaya = \App\Models\AktivitasPekerjaan::where('id_kunjungan', $kunjungan->id_kunjungan)
                            ->where('id_engineer', $engSaya->id_engineer)
                            ->whereNotNull('waktu_mulai')->exists();
                    }
                }
            @endphp
            @if(!$sudahCheckinSaya)
            <!-- Kotak Check-In GPS -->
            <div class="p-6 rounded-2xl bg-blue-50 border border-blue-200 text-center space-y-3 shadow-sm">
                <h4 class="text-sm font-bold text-[#002266]">Sudah Tiba di Lokasi Klien?</h4>
                <p class="text-xs text-slate-600 font-medium">Klik tombol di bawah ini untuk mencatat koordinat GPS dan memulai pengerjaan.</p>
                
                <form id="formCheckIn" action="{{ route('kunjungan.checkin', $kunjungan->nomor) }}" method="POST">
                    @csrf
                    <input type="hidden" name="lokasi_gps" id="lokasi_gps_checkin">
                    <button type="button" onclick="getGPSCheckIn()" class="w-full max-w-md mx-auto px-4 py-3 bg-[#002266] hover:bg-[#001233] text-white rounded-xl text-xs font-bold shadow-lg shadow-blue-900/20 transition">
                        📍 Ambil Lokasi GPS & Check-In
                    </button>
                </form>
            </div>
            @endif
        @endif
    @endif

    @if($kunjungan->status == 'Reschedule')
        <div class="p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-700 text-xs flex flex-col gap-1 shadow-sm">
            <span class="font-bold">⚠️ Status: Jadwal Perlu Dijadwalkan Ulang (Reschedule)</span>
            <p class="font-medium">Alasan penolakan dari Engineer: "{{ $kunjungan->alasan_reschedule }}"</p>
        </div>
    @endif

    <!-- SECTION 2: Pelaksanaan & Upload Dokumentasi Foto Lapangan -->
    @if(Auth::user()->id_role == 3 && $kunjungan->status == 'Dikerjakan')
        <div class="p-5 md:p-6 rounded-2xl bg-white border border-slate-200 shadow-sm space-y-4">
            <h4 class="text-sm font-bold text-[#002266] flex items-center gap-2">
                📷 Unggah Dokumentasi Lapangan (On-Site)
            </h4>
            <form action="{{ route('kunjungan.dokumentasi', $kunjungan->nomor) }}" method="POST" enctype="multipart/form-data" class="space-y-4 text-xs font-medium">
                @csrf
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-slate-700 mb-1.5 font-bold">Kategori Foto</label>
                        <select name="kategori_foto" class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl text-slate-800 focus:ring-[#003399]">
                            <option value="Sebelum">Foto Sebelum Pengerjaan</option>
                            <option value="Proses">Foto Saat Pengerjaan</option>
                            <option value="Sesudah">Foto Setelah Selesai</option>
                            <option value="Lainnya">Foto Lainnya / Kondisi Khusus</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-slate-700 mb-1.5 font-bold">Pilih Foto/Video (Kamera atau Galeri HP)</label>
                        <input type="file" name="foto" accept="image/*,video/*" required class="w-full text-slate-600 file:mr-3 file:py-1.5 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-[#002266] file:text-white hover:file:bg-[#001233]">
                    </div>
                </div>
                <div>
                    <label class="block text-slate-700 mb-1.5 font-bold">Keterangan Foto</label>
                    <input type="text" name="keterangan" placeholder="Contoh: Kondisi port switch sebelum pergantian modul" class="w-full px-3 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-slate-800 focus:ring-[#003399]">
                </div>
                <button type="submit" class="px-5 py-2.5 bg-[#0044cc] hover:bg-[#003399] text-white rounded-xl font-bold shadow-md">Unggah Foto Dokumentasi</button>
            </form>
        </div>
    @endif

    <!-- SECTION 2.5: Form Pencatatan Pengeluaran (Expense) -->
    @if(Auth::user()->id_role == 3 && $kunjungan->status == 'Dikerjakan')
        <div class="p-5 md:p-6 rounded-2xl bg-white border border-slate-200 shadow-sm space-y-4">
            <h4 class="text-sm font-bold text-slate-800 flex items-center gap-2">
                💸 Catat Pengeluaran Operasional
            </h4>
            <form action="{{ route('kunjungan.pengeluaran', $kunjungan->nomor) }}" method="POST" enctype="multipart/form-data" class="space-y-4 text-xs font-medium">
                @csrf
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-slate-700 mb-1.5 font-bold">Jenis Biaya</label>
                        <select name="jenis_biaya" class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl text-slate-800 focus:ring-[#003399]">
                            <option value="Bensin">Bensin / BBM</option>
                            <option value="Tol">Tol</option>
                            <option value="Parkir">Parkir</option>
                            <option value="Makan">Makan (Konsumsi)</option>
                            <option value="Pembelian Alat">Pembelian Alat Dadakan</option>
                            <option value="Lainnya">Lainnya</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-slate-700 mb-1.5 font-bold">Nominal (Rp)</label>
                        <input type="number" name="nominal" min="0" required placeholder="Contoh: 50000" class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl text-slate-800 focus:ring-[#003399]">
                    </div>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-slate-700 mb-1.5 font-bold">Bukti Foto/Video Nota / Struk (Opsional)</label>
                        <input type="file" name="bukti_nota" accept="image/*,video/*" class="w-full text-slate-600 file:mr-3 file:py-1.5 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-slate-200 file:text-slate-800 hover:file:bg-slate-300">
                    </div>
                    <div>
                        <label class="block text-slate-700 mb-1.5 font-bold">Keterangan Tambahan</label>
                        <input type="text" name="keterangan" placeholder="Contoh: Beli kabel LAN 5 meter" class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl text-slate-800 focus:ring-[#003399]">
                    </div>
                </div>
                <button type="submit" class="px-5 py-2.5 bg-slate-800 hover:bg-slate-700 text-white rounded-xl font-bold shadow-md">Simpan Pengeluaran</button>
            </form>
        </div>
    @endif

    <!-- SECTION 3: Form Check-Out (muncul setelah laporan dibuat & TTD terkunci) -->
    @php
        $myEngineer = Auth::user()->id_role == 3 ? \App\Models\Engineer::where('id_pengguna', Auth::user()->id_pengguna)->first() : null;
        $myAktivitas = $myEngineer ? $kunjungan->aktivitas->where('id_engineer', $myEngineer->id_engineer)->sortByDesc('created_at')->first() : null;
        $sudahCheckin = $myAktivitas && $myAktivitas->waktu_mulai;
        $sudahCheckout = $myAktivitas && $myAktivitas->waktu_selesai;
    @endphp
    @if(Auth::user()->id_role == 3 && $kunjungan->status == 'Dikerjakan' && !$sudahCheckout && $kunjungan->laporan && $kunjungan->laporan->buktiPenyelesaian)
        <div class="p-5 md:p-6 rounded-2xl bg-blue-50 border border-blue-200 shadow-sm space-y-4">
            <h4 class="text-sm font-bold text-[#002266]">📝 Input Catatan & Check-Out</h4>
            <p class="text-xs text-slate-600 font-medium">Tuliskan ringkasan hasil pengerjaan. Sistem akan memverifikasi lokasi GPS Anda untuk proses Check-Out.</p>
            
            <form id="formCheckOut" action="{{ route('kunjungan.checkout', $kunjungan->nomor) }}" method="POST" class="space-y-4 text-xs">
                @csrf
                <input type="hidden" name="lokasi_gps" id="lokasi_gps_checkout"> 
                
                <div>
                    <label class="block text-slate-700 font-bold mb-1.5">Deskripsi / Hasil Pekerjaan Lapangan:</label>
                    <textarea name="catatan" id="catatan_pekerjaan" rows="4" required placeholder="Contoh: Pemeliharaan berkala server dan perapihan cabling rack selesai 100%." class="w-full p-3 bg-white border border-slate-300 rounded-xl text-slate-800 focus:outline-none focus:ring-2 focus:ring-[#003399]"></textarea>
                </div>
                
                <button type="button" onclick="getGPSCheckOut()" class="w-full py-3 bg-emerald-600 hover:bg-emerald-500 text-white font-bold rounded-xl shadow-lg shadow-emerald-600/20 transition">
                    📍 Ambil GPS & Check-Out
                </button>
            </form>
        </div>
    @endif

    <!-- SECTION 3a: Buat Laporan (hanya 1x - siapa cepat dia dapat, setelah check-in) -->
    @if(Auth::user()->id_role == 3 && $sudahCheckin && !$kunjungan->laporan)
        <div class="p-5 md:p-6 rounded-2xl bg-amber-50 border border-amber-200 shadow-sm space-y-4 text-center">
            <h4 class="text-sm font-bold text-[#002266]">📄 Buat Laporan Kunjungan</h4>
            <p class="text-xs text-slate-600 font-medium">Anda sudah check-in. Klik tombol di bawah untuk membuat laporan. <span class="font-bold text-amber-700">Hanya 1 laporan per kunjungan</span> — siapa yang klik duluan, dia yang jadi pembuatnya.</p>
            <form action="{{ route('kunjungan.buat-laporan', $kunjungan->nomor) }}" method="POST">
                @csrf
                <button type="submit" class="w-full py-3 bg-[#003399] hover:bg-[#002266] text-white font-bold rounded-xl shadow-lg transition">
                    📄 Buat Laporan Sekarang
                </button>
            </form>
        </div>
    @endif
    @if($kunjungan->laporan && $kunjungan->laporan->pembuat)
        <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200 text-xs text-slate-600 font-medium text-center shadow-sm">
            📄 Laporan dibuat oleh <span class="font-bold text-[#003399]">{{ $kunjungan->laporan->pembuat->user->nama ?? '-' }}</span>
        </div>
    @endif

    <!-- SECTION 3b: Isi / Revisi Laporan (tampil setelah laporan dibuat, sebelum TTD) -->
    @if(Auth::user()->id_role == 3 && $kunjungan->laporan && !$kunjungan->laporan->buktiPenyelesaian)
        <div class="p-5 md:p-6 rounded-2xl bg-white border border-slate-200 shadow-sm space-y-4">
            <h4 class="text-sm font-bold text-[#002266]">📝 Isi Laporan Pekerjaan</h4>
            <p class="text-xs text-slate-600 font-medium">Tulis hasil pekerjaan dengan bahasa yang mudah dipahami customer. Isi ini tampil di laporan PDF dan <span class="font-bold">otomatis mengikuti revisi terbaru</span>.</p>

            <form action="{{ route('kunjungan.revisi-catatan', $kunjungan->nomor) }}" method="POST" class="space-y-3">
                @csrf
                <div>
                    <label class="block text-slate-700 text-xs font-bold mb-1.5">Hasil Pekerjaan <span class="text-red-500">*</span></label>
                    <textarea name="hasil_pekerjaan" rows="3" required placeholder="Contoh: Pemeliharaan server selesai. Sistem berjalan normal dan sudah dites bersama PIC." class="w-full p-3 bg-white border border-slate-300 rounded-xl text-xs text-slate-800 focus:outline-none focus:ring-2 focus:ring-[#003399]">{{ old('hasil_pekerjaan', $kunjungan->laporan->hasil_pekerjaan ?? '') }}</textarea>
                </div>
                <div>
                    <label class="block text-slate-700 text-xs font-bold mb-1.5">Catatan Tambahan <span class="text-slate-400 font-normal">(opsional)</span></label>
                    <textarea name="catatan_tambahan" rows="2" placeholder="Contoh: Disarankan penggantian kabel LAN lantai 2 bulan depan." class="w-full p-3 bg-white border border-slate-300 rounded-xl text-xs text-slate-800 focus:outline-none focus:ring-2 focus:ring-[#003399]">{{ old('catatan_tambahan', $kunjungan->laporan->catatan_tambahan ?? '') }}</textarea>
                </div>

                <button type="submit" class="px-5 py-2.5 bg-[#003399] hover:bg-[#002266] text-white rounded-xl text-xs font-bold shadow-md transition">
                    💾 Simpan Laporan
                </button>
            </form>
        </div>
    @endif

    <!-- SECTION 4: Kotak Tanda Tangan Digital Khusus Customer -->
    @if((($kunjungan->status == 'Dikerjakan' &&$kunjungan->laporan) || ($kunjungan->laporan && !$kunjungan->laporan->buktiPenyelesaian)))
        <div class="p-5 md:p-6 rounded-2xl bg-amber-50 border border-amber-200 shadow-sm space-y-4">
            <div class="flex items-center gap-2">
                <span class="w-3 h-3 rounded-full bg-amber-500 animate-ping"></span>
                <h4 class="text-sm font-bold text-amber-700">✍ Verifikasi Tanda Tangan Customer & Engineer</h4>
            </div>
            <p class="text-xs text-amber-800 font-medium">Silakan sodorkan HP ke Customer / PIC <strong>({{ $kunjungan->customer->pic ?? 'PIC Perusahaan' }})</strong> untuk membubuhkan tanda tangan pada kotak pertama, lalu Engineer membubuhkan tanda tangan pada kotak kedua:</p>

            <form id="signatureForm" action="{{ route('kunjungan.signature', $kunjungan->nomor) }}" method="POST" class="space-y-4">
                @csrf
                <input type="hidden" name="signature" id="signatureInput">
                <input type="hidden" name="signature_engineer" id="signatureEngineerInput">

                <div>
                    <p class="text-xs font-bold text-slate-700 mb-1.5">Tanda Tangan Customer / PIC:</p>
                    <div id="sigWrapCustomer" data-locked="1" class="relative border-2 border-slate-300 bg-white rounded-xl overflow-hidden shadow-inner touch-pan-x touch-pan-y">
                        <canvas id="signaturePad" class="w-full h-56 block cursor-crosshair"></canvas>
                        <div id="sigBadgeCustomer" class="absolute top-2 right-2 px-2 py-1 rounded-lg text-[11px] font-bold pointer-events-none bg-slate-800/70 text-white">🔒 Terkunci</div>
                    </div>
                    <div class="mt-2 flex flex-col sm:flex-row gap-2">
                        <button type="button" id="lockBtnCustomer" onclick="togglePadLock('customer')" class="w-full sm:w-auto px-4 py-2 bg-amber-100 border border-amber-300 text-amber-800 rounded-xl text-xs font-bold hover:bg-amber-200 transition">
                            🔓 Buka Kunci TTD
                        </button>
                        <button type="button" onclick="clearSignature()" class="w-full sm:w-auto px-4 py-2 bg-white border border-slate-300 text-slate-700 rounded-xl text-xs font-bold hover:bg-slate-50 transition">
                            Hapus & Ulangi TTD Customer
                        </button>
                    </div>
                </div>

                <div>
                    <p class="text-xs font-bold text-slate-700 mb-1.5">Tanda Tangan Engineer ({{ $kunjungan->engineer->user->nama ?? 'Engineer' }}):</p>
                    <div id="sigWrapEngineer" data-locked="1" class="relative border-2 border-slate-300 bg-white rounded-xl overflow-hidden shadow-inner touch-pan-x touch-pan-y">
                        <canvas id="signaturePadEngineer" class="w-full h-56 block cursor-crosshair"></canvas>
                        <div id="sigBadgeEngineer" class="absolute top-2 right-2 px-2 py-1 rounded-lg text-[11px] font-bold pointer-events-none bg-slate-800/70 text-white">🔒 Terkunci</div>
                    </div>
                    <div class="mt-2 flex flex-col sm:flex-row gap-2">
                        <button type="button" id="lockBtnEngineer" onclick="togglePadLock('engineer')" class="w-full sm:w-auto px-4 py-2 bg-amber-100 border border-amber-300 text-amber-800 rounded-xl text-xs font-bold hover:bg-amber-200 transition">
                            🔓 Buka Kunci TTD
                        </button>
                        <button type="button" onclick="clearSignatureEngineer()" class="w-full sm:w-auto px-4 py-2 bg-white border border-slate-300 text-slate-700 rounded-xl text-xs font-bold hover:bg-slate-50 transition">
                            Hapus & Ulangi TTD Engineer
                        </button>
                    </div>
                </div>

                <div class="flex flex-col sm:flex-row items-center justify-end gap-3 pt-1">
                    <button type="button" onclick="submitSignature()" class="w-full sm:w-auto px-6 py-2.5 bg-[#002266] hover:bg-[#001233] text-white font-bold rounded-xl text-xs shadow-lg shadow-blue-900/20 transition">
                        Simpan & Kunci Tanda Tangan
                    </button>
                </div>
            </form>
        </div>
    @endif

    <!-- SECTION 5: Bukti Dokumen Terverifikasi -->
    @if($kunjungan->laporan &&$kunjungan->laporan->buktiPenyelesaian)
        <div class="p-5 md:p-6 rounded-2xl bg-emerald-50 border border-emerald-200 shadow-sm space-y-4">
            <div class="flex flex-col sm:flex-row items-center justify-between gap-4">
                <div>
                    <h4 class="text-sm font-bold text-emerald-700">Pekerjaan Selesai & Dokumen Terverifikasi Resmi</h4>
                    <p class="text-xs text-emerald-600 font-medium mt-1">Ditandatangani oleh PIC pada: {{ $kunjungan->laporan->buktiPenyelesaian->tanggal_tanda_tangan }}</p>
                </div>
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div class="bg-white p-2 rounded-xl border border-slate-200 shadow-sm text-center">
                    <p class="text-[11px] font-bold text-slate-500 mb-1">TTD Customer</p>
                    <img src="{{ $kunjungan->laporan->buktiPenyelesaian->tanda_tangan_customer }}" alt="Customer Signature" class="h-14 object-contain mx-auto">
                </div>
                <div class="bg-white p-2 rounded-xl border border-slate-200 shadow-sm text-center">
                    <p class="text-[11px] font-bold text-slate-500 mb-1">TTD Engineer</p>
                    @if($kunjungan->laporan->buktiPenyelesaian->tanda_tangan_engineer)
                    <img src="{{ $kunjungan->laporan->buktiPenyelesaian->tanda_tangan_engineer }}" alt="Engineer Signature" class="h-14 object-contain mx-auto">
                    @else
                    <p class="text-[11px] italic text-slate-400">-</p>
                    @endif
                </div>
            </div>
        </div>
    @endif

    <!-- SECTION 6: Detail Informasi Tiket, Tools & Log GPS -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="p-5 rounded-2xl bg-white border border-slate-200 shadow-sm space-y-3 text-xs">
            <h4 class="text-xs font-bold text-[#002266] uppercase tracking-wider border-b border-slate-100 pb-2">Detail Informasi Tiket</h4>
            <div class="space-y-2 font-medium text-slate-600">
                <p><strong class="text-slate-800">Customer:</strong>
                    @if($kunjungan->customer)
                        <a href="{{ route('master.customer.show', $kunjungan->customer->kode) }}" class="text-[#003399] font-bold hover:underline">{{ $kunjungan->customer->nama_perusahaan }}</a>
                        <span class="font-mono text-[10px] text-slate-400 font-bold">({{ $kunjungan->customer->kode }})</span>
                    @else
                        -
                    @endif
                </p>
                <p><strong class="text-slate-800">PIC:</strong> {{ $kunjungan->customer->pic ?? '-' }} ({{ $kunjungan->customer->telepon ?? '-' }})</p>
                <p><strong class="text-slate-800">Lead Engineer:</strong> {{ $kunjungan->engineer->user->nama ?? 'Belum Ditugaskan' }}</p>
                <p><strong class="text-slate-800">Tim Support:</strong> 
                    @if($kunjungan->supportEngineers->count() > 0)
                        {{$kunjungan->supportEngineers->pluck('user.nama')->implode(', ') }}
                    @else
                        <span class="italic text-slate-400">Tidak ada tim support</span>
                    @endif
                </p>
                <p><strong class="text-slate-800">Alat Kerja Terbawa:</strong></p>
                <ul class="list-disc list-inside pl-2">
                    @forelse($kunjungan->tools as $tool)
                        <li><a href="{{ route('master.tool.show', $tool->kode) }}" class="text-[#003399] font-bold hover:underline">{{ $tool->nama_alat }}</a> <span class="font-mono text-[10px] text-slate-400 font-bold">({{ $tool->kode }})</span></li>
                    @empty
                        <li class="italic text-slate-400">Tidak ada tools khusus</li>
                    @endforelse
                </ul>
            </div>
        </div>

        <div class="p-5 rounded-2xl bg-white border border-slate-200 shadow-sm space-y-5 text-xs">
            <h4 class="text-xs font-bold text-[#002266] uppercase tracking-wider border-b border-slate-100 pb-2">Log Waktu & Lokasi GPS</h4>
            @forelse($kunjungan->aktivitas->sortBy('waktu_mulai') as $act)
                @php
                    $namaEng = $act->engineer->user->nama ?? '-';
                    $isLead = $act->id_engineer == $kunjungan->id_engineer;
                    $ciLat = $coLat = $ciLng = $coLng = null;
                    if ($act->lokasi) {
                        $ciParts = explode(',', $act->lokasi);
                        $ciLat = trim($ciParts[0] ?? '');
                        $ciLng = trim($ciParts[1] ?? '');
                    }
                    if ($act->lokasi_checkout) {
                        $coParts = explode(',', $act->lokasi_checkout);
                        $coLat = trim($coParts[0] ?? '');
                        $coLng = trim($coParts[1] ?? '');
                    }
                    // Fallback ke koordinat global kunjungan untuk lead
                    if ($isLead) {
                        if (!$ciLat && $kunjungan->check_in_latitude) { $ciLat = $kunjungan->check_in_latitude; $ciLng = $kunjungan->check_in_longitude; }
                        if (!$coLat && $kunjungan->check_out_latitude) { $coLat = $kunjungan->check_out_latitude; $coLng = $kunjungan->check_out_longitude; }
                    }
                @endphp
                <div class="p-4 rounded-xl bg-slate-50 border border-slate-200 space-y-4">
                    <div class="flex items-center justify-between">
                        <p class="font-bold text-sm text-slate-800">👷 {{ $namaEng }}</p>
                        <span class="px-2.5 py-1 rounded-full text-[10px] font-bold {{ $isLead ? 'bg-[#002266] text-white' : 'bg-slate-200 text-slate-600' }}">{{ $isLead ? 'LEAD ENGINEER' : 'TIM SUPPORT' }}</span>
                    </div>

                    <!-- Lokasi Check-in -->
                    <div class="space-y-2">
                        <div class="flex items-center justify-between gap-2">
                            <p class="font-bold text-slate-800">📍 Lokasi Check-in</p>
                            @if($ciLat && $ciLng)
                            <a href="https://www.google.com/maps/search/?api=1&query={{ $ciLat }},{{ $ciLng }}" target="_blank" class="px-3 py-1.5 bg-white hover:bg-slate-100 text-slate-700 rounded-xl text-[11px] font-bold transition flex items-center gap-1.5 border border-slate-200">
                                🗺️ Buka Google Maps
                            </a>
                            @endif
                        </div>
                        @if($ciLat && $ciLng)
                            <p class="font-mono font-bold text-[#003399]">{{ $ciLat }}, {{ $ciLng }}</p>
                            <div class="w-full h-48 rounded-xl overflow-hidden border border-slate-200 shadow-inner">
                                <iframe class="w-full h-full border-0" loading="lazy" allowfullscreen
                                    src="https://maps.google.com/maps?q={{ $ciLat }},{{ $ciLng }}&z=16&output=embed"></iframe>
                            </div>
                        @else
                            <p class="italic text-slate-400 font-medium">Belum check-in</p>
                        @endif
                        <p class="font-medium text-slate-600"><strong class="text-slate-800">Waktu Check-in:</strong> {{ $act->waktu_mulai ?? '-' }}</p>
                    </div>

                    <!-- Lokasi Check-out -->
                    <div class="space-y-2 border-t border-slate-200 pt-4">
                        <div class="flex items-center justify-between gap-2">
                            <p class="font-bold text-slate-800">📍 Lokasi Check-out</p>
                            @if($coLat && $coLng)
                            <a href="https://www.google.com/maps/search/?api=1&query={{ $coLat }},{{ $coLng }}" target="_blank" class="px-3 py-1.5 bg-white hover:bg-slate-100 text-slate-700 rounded-xl text-[11px] font-bold transition flex items-center gap-1.5 border border-slate-200">
                                🗺️ Buka Google Maps
                            </a>
                            @endif
                        </div>
                        @if($coLat && $coLng)
                            <p class="font-mono font-bold text-[#003399]">{{ $coLat }}, {{ $coLng }}</p>
                            <div class="w-full h-48 rounded-xl overflow-hidden border border-slate-200 shadow-inner">
                                <iframe class="w-full h-full border-0" loading="lazy" allowfullscreen
                                    src="https://maps.google.com/maps?q={{ $coLat }},{{ $coLng }}&z=16&output=embed"></iframe>
                            </div>
                        @else
                            <p class="italic text-slate-400 font-medium">Belum check-out</p>
                        @endif
                        <p class="font-medium text-slate-600"><strong class="text-slate-800">Waktu Check-out:</strong> {{ $act->waktu_selesai ?? '-' }}</p>
                    </div>

                    <p class="font-medium text-slate-600 border-t border-slate-200 pt-3"><strong class="text-slate-800">Catatan Engineer:</strong> {{ $act->catatan ?? '-' }}</p>
                </div>
            @empty
                <p class="italic text-slate-400 font-medium">Belum ada aktivitas check-in.</p>
            @endforelse
        </div>
    </div>

    <!-- Tabel Rincian Pengeluaran -->
    <div class="p-5 md:p-6 rounded-2xl bg-white border border-slate-200 shadow-sm">
        <h4 class="text-sm font-bold text-[#002266] mb-4">Rincian Pengeluaran Operasional</h4>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-600">
                <thead class="text-[11px] uppercase bg-slate-50 text-slate-500 border-b border-slate-200">
                    <tr>
                        <th class="p-3 font-bold">Jenis Biaya</th>
                        <th class="p-3 font-bold">Nominal (Rp)</th>
                        <th class="p-3 font-bold">Keterangan</th>
                        <th class="p-3 font-bold">Bukti Nota</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @php $totalPengeluaran = 0; @endphp
                    @forelse($kunjungan->pengeluaran as $peng)
                        @php $totalPengeluaran +=$peng->nominal; @endphp
                        <tr class="hover:bg-slate-50 font-medium">
                            <td class="p-3 font-bold text-slate-800">{{ $peng->jenis_biaya }}</td>
                            <td class="p-3 text-emerald-600 font-bold">Rp {{ number_format($peng->nominal, 0, ',', '.') }}</td>
                            <td class="p-3">{{ $peng->keterangan ?? '-' }}</td>
                            <td class="p-3">
                                @if($peng->bukti_nota)
                                    <a href="{{ asset($peng->bukti_nota) }}" target="_blank" class="text-[#0044cc] hover:underline font-bold">Lihat Bukti</a>
                                @else
                                    <span class="text-slate-400 italic">Tidak ada struk</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="p-4 text-center text-slate-400 font-medium italic">Belum ada catatan pengeluaran.</td>
                        </tr>
                    @endforelse
                </tbody>
                @if($totalPengeluaran > 0)
                    <tfoot>
                        <tr class="bg-slate-50 font-bold border-t-2 border-slate-200">
                            <td class="p-3 text-right text-slate-800">TOTAL KESELURUHAN:</td>
                            <td class="p-3 text-emerald-600 text-sm">Rp {{ number_format($totalPengeluaran, 0, ',', '.') }}</td>
                            <td colspan="2"></td>
                        </tr>
                    </tfoot>
                @endif
            </table>
        </div>
    </div>

    <!-- Galeri Foto Lapangan -->
    <div class="p-5 md:p-6 rounded-2xl bg-white border border-slate-200 shadow-sm">
        <h4 class="text-sm font-bold text-[#002266] mb-4">Galeri Dokumentasi On-Site</h4>
        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-4">
            @forelse($kunjungan->dokumentasi as $doc)
                <div class="rounded-xl overflow-hidden bg-slate-50 border border-slate-200 shadow-sm">
                    @php $ext = strtolower(pathinfo($doc->file_foto, PATHINFO_EXTENSION)); @endphp
                    @if(in_array($ext, ['mp4','mov','3gp','webm']))
                        <video src="{{ asset($doc->file_foto) }}" controls class="w-full h-32 object-cover bg-black"></video>
                    @else
                        <img src="{{ asset($doc->file_foto) }}" alt="Dokumentasi" class="w-full h-32 object-cover">
                    @endif
                    <div class="p-2 text-[10px]">
                        <span class="px-2 py-0.5 rounded bg-blue-100 text-[#003399] font-bold uppercase">{{ $doc->kategori_foto }}</span>
                        <p class="text-slate-600 mt-1.5 font-medium truncate">{{ $doc->keterangan ?? '-' }}</p>
                    </div>
                </div>
            @empty
                <div class="col-span-full text-center py-6 text-slate-400 text-xs font-medium italic">Belum ada foto dokumentasi diunggah.</div>
            @endforelse
        </div>
    </div>

</div>

<!-- MODAL INFO KHUSUS ALERT -->
<div id="modalInfoGPS" class="hidden fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-[60] flex items-center justify-center p-4">
    <div class="bg-white border border-slate-200 rounded-2xl w-full max-w-xs p-6 shadow-2xl text-center transition-all">
        <div id="modalInfoIcon" class="mx-auto flex items-center justify-center h-14 w-14 rounded-full mb-4">
        </div>
        <h3 id="modalInfoTitle" class="text-base font-bold text-[#002266] mb-2"></h3>
        <p id="modalInfoMessage" class="text-xs text-slate-600 mb-6 font-medium leading-relaxed"></p>
        <button type="button" onclick="document.getElementById('modalInfoGPS').classList.add('hidden')" class="w-full px-4 py-2.5 bg-[#002266] hover:bg-[#001233] text-white font-bold rounded-xl text-xs transition">
            Tutup
        </button>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/signature_pad@4.0.0/dist/signature_pad.umd.min.js"></script>
<script>
    function showGPSModal(title, message, isSuccess) {
        document.getElementById('modalInfoTitle').innerText = title;
        document.getElementById('modalInfoMessage').innerText = message;
        
        const iconContainer = document.getElementById('modalInfoIcon');
        if(isSuccess) {
            iconContainer.className = 'mx-auto flex items-center justify-center h-14 w-14 rounded-full mb-4 bg-emerald-100 text-emerald-600 border border-emerald-200';
            iconContainer.innerHTML = '<svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>';
        } else {
            iconContainer.className = 'mx-auto flex items-center justify-center h-14 w-14 rounded-full mb-4 bg-rose-100 text-rose-600 border border-rose-200';
            iconContainer.innerHTML = '<svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>';
        }
        
        document.getElementById('modalInfoGPS').classList.remove('hidden');
    }

    function getGPSCheckIn() {
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(
                (position) => {
                    const coords = `${position.coords.latitude}, ${position.coords.longitude}`;
                    document.getElementById('lokasi_gps_checkin').value = coords;
                    showConfirmModal(
                        document.getElementById('formCheckIn'),
                        'Konfirmasi Check-In',
                        `Koordinat Anda: ${coords}. Apakah Anda yakin ingin melakukan Check-In sekarang?`
                    );
                },
                (error) => {
                    showGPSModal('Gagal Membaca GPS', 'Pastikan GPS aktif dan izin lokasi (Location) di browser HP Anda telah diizinkan.', false);
                },
                { enableHighAccuracy: true }
            );
        } else {
            showGPSModal('Tidak Mendukung', 'Perangkat Anda tidak mendukung fitur geolokasi.', false);
        }
    }

    function getGPSCheckOut() {
        const catatan = document.getElementById('catatan_pekerjaan').value;
        if (!catatan.trim()) {
            showGPSModal('Deskripsi Kosong', 'Harap isi deskripsi hasil pekerjaan terlebih dahulu sebelum melakukan Check-Out!', false);
            return;
        }

        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(
                (position) => {
                    const coords = `${position.coords.latitude}, ${position.coords.longitude}`;
                    document.getElementById('lokasi_gps_checkout').value = coords;
                    showConfirmModal(
                        document.getElementById('formCheckOut'),
                        'Konfirmasi Check-Out',
                        `Koordinat GPS Check-Out terdeteksi: ${coords}. Apakah Anda yakin ingin mengakhiri pekerjaan ini?`
                    );
                },
                (error) => {
                    showGPSModal('Gagal Membaca GPS', 'Pastikan GPS aktif dan izin lokasi di browser HP Anda telah diizinkan.', false);
                },
                { enableHighAccuracy: true }
            );
        } else {
            showGPSModal('Tidak Mendukung', 'Perangkat Anda tidak mendukung fitur geolokasi.', false);
        }
    }

    let signaturePad, signaturePadEngineer;
    // Draft TTD dari server (tersimpan otomatis), direstore agar tidak hilang saat refresh
    const draftTtdCustomer = @json($kunjungan->draft_ttd_customer);
    const draftTtdEngineer = @json($kunjungan->draft_ttd_engineer);
    const kunjunganId = @json($kunjungan->nomor);
    const draftTimers = {};

    function initPad(canvasId, draftDataUrl) {
        const canvas = document.getElementById(canvasId);
        if (!canvas) return null;
        const pad = new SignaturePad(canvas, {
            backgroundColor: 'rgb(255, 255, 255)',
            penColor: 'rgb(0, 0, 0)'
        });
        pad._draftUrl = draftDataUrl || null;
        // Flag isi manual: isEmpty() bawaan tidak mendeteksi gambar hasil restore
        pad._hasContent = !!draftDataUrl;
        pad._restoring = false;
        function restoreDraft() {
            if (!pad._draftUrl) return;
            pad._restoring = true;
            const img = new Image();
            img.onload = () => {
                try {
                    // Canvas sudah di-scale ratio; gambar pas 1:1 piksel
                    canvas.getContext('2d').drawImage(img, 0, 0, canvas.offsetWidth, canvas.offsetHeight);
                    pad._hasContent = true;
                } catch (e) { console.error(e); }
                pad._restoring = false;
            };
            img.onerror = () => { pad._restoring = false; };
            img.src = pad._draftUrl;
        }
        function resizeCanvas() {
            const ratio = Math.max(window.devicePixelRatio || 1, 1);
            canvas.width = canvas.offsetWidth * ratio;
            canvas.height = canvas.offsetHeight * ratio;
            canvas.getContext("2d").scale(ratio, ratio);
            pad.clear();
            restoreDraft();
        }
        // Simpan draft otomatis setiap selesai menggores (debounce 1 detik)
        pad.addEventListener('endStroke', () => {
            pad._hasContent = true;
            const key = canvasId;
            clearTimeout(draftTimers[key]);
            draftTimers[key] = setTimeout(() => {
                saveDraft(canvasId === 'signaturePad' ? 'customer' : 'engineer');
            }, 1000);
        });
        window.addEventListener("resize", resizeCanvas);
        resizeCanvas();
        return pad;
    }

    // Cek isi pad: gabungan flag manual + isEmpty() bawaan
    function padIsEmpty(pad) {
        return !(pad && (pad._hasContent || !pad.isEmpty()));
    }

    // Kirim draft TTD ke server agar tidak hilang saat refresh
    async function saveDraft(which) {
        const isCustomer = which === 'customer';
        const pad = isCustomer ? signaturePad : signaturePadEngineer;
        // Jangan simpan saat gambar restore masih loading (dataURL belum lengkap)
        if (!pad || pad._restoring) return;
        const dataUrl = padIsEmpty(pad) ? null : pad.toDataURL();
        pad._draftUrl = dataUrl;
        const tokenEl = document.querySelector('#signatureForm input[name="_token"]');
        try {
            await fetch(`/kunjungan/${kunjunganId}/signature-draft`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': tokenEl ? tokenEl.value : '',
                    'Accept': 'application/json',
                },
                body: JSON.stringify({ type: which, signature: dataUrl }),
            });
        } catch (e) {
            console.error('Gagal menyimpan draft TTD:', e);
        }
    }

    document.addEventListener('DOMContentLoaded', () => {
        signaturePad = initPad('signaturePad', draftTtdCustomer);
        signaturePadEngineer = initPad('signaturePadEngineer', draftTtdEngineer);
        // Default terkunci agar scroll HP tidak mencoret TTD tanpa sengaja
        setPadLock('customer', true);
        setPadLock('engineer', true);
    });

    function setPadLock(which, locked) {
        const isCustomer = which === 'customer';
        const pad = isCustomer ? signaturePad : signaturePadEngineer;
        const wrap = document.getElementById(isCustomer ? 'sigWrapCustomer' : 'sigWrapEngineer');
        const btn = document.getElementById(isCustomer ? 'lockBtnCustomer' : 'lockBtnEngineer');
        const badge = document.getElementById(isCustomer ? 'sigBadgeCustomer' : 'sigBadgeEngineer');
        if (!pad || !wrap || !btn || !badge) return;
        wrap.dataset.locked = locked ? '1' : '0';
        if (locked) { pad.off(); saveDraft(which); } else { pad.on(); }
        wrap.classList.toggle('touch-none', !locked);
        wrap.classList.toggle('touch-pan-x', locked);
        wrap.classList.toggle('touch-pan-y', locked);
        badge.textContent = locked ? '🔒 Terkunci' : '🔓 Mode Tanda Tangan';
        badge.className = 'absolute top-2 right-2 px-2 py-1 rounded-lg text-[11px] font-bold pointer-events-none ' + (locked ? 'bg-slate-800/70 text-white' : 'bg-emerald-600/85 text-white');
        btn.innerHTML = locked ? '🔓 Buka Kunci TTD' : '🔒 Kunci TTD';
    }

    function togglePadLock(which) {
        const isCustomer = which === 'customer';
        const wrap = document.getElementById(isCustomer ? 'sigWrapCustomer' : 'sigWrapEngineer');
        if (!wrap) return;
        setPadLock(which, wrap.dataset.locked !== '1');
    }

    function clearSignature() {
        if (signaturePad) { signaturePad.clear(); signaturePad._hasContent = false; saveDraft('customer'); }
    }

    function clearSignatureEngineer() {
        if (signaturePadEngineer) { signaturePadEngineer.clear(); signaturePadEngineer._hasContent = false; saveDraft('engineer'); }
    }

    function submitSignature() {
        if (padIsEmpty(signaturePad)) {
            showGPSModal('Tanda Tangan Kosong', 'Customer belum membubuhkan tanda tangan. Silakan isi terlebih dahulu pada kotak TTD Customer.', false);
            return;
        }
        if (padIsEmpty(signaturePadEngineer)) {
            showGPSModal('Tanda Tangan Kosong', 'Engineer belum membubuhkan tanda tangan. Silakan isi terlebih dahulu pada kotak TTD Engineer.', false);
            return;
        }
        document.getElementById('signatureInput').value = signaturePad.toDataURL();
        document.getElementById('signatureEngineerInput').value = signaturePadEngineer.toDataURL();
        showConfirmModal(
            document.getElementById('signatureForm'),
            'Kunci Laporan',
            'Dengan menandatangani dokumen ini, pekerjaan dinyatakan selesai secara resmi. Lanjutkan?'
        );
    }
</script>

    <!-- MODAL PREVIEW PDF -->
    <div id="modalPreviewPdf" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4">
        <div class="absolute inset-0 bg-black/60" onclick="tutupPreviewPdf()"></div>
        <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-3xl max-h-[90vh] flex flex-col overflow-hidden">
            <div class="flex items-center justify-between px-5 py-3 border-b border-slate-200">
                <h4 class="text-sm font-bold text-[#002266]">👁️ Preview Laporan PDF</h4>
                <button type="button" onclick="tutupPreviewPdf()" class="text-slate-400 hover:text-slate-700 text-xl leading-none">✕</button>
            </div>
            <div class="flex-1 overflow-y-auto bg-slate-200 p-3 max-h-[60vh]" id="pdfPreviewBody">
                <div id="pdfPreviewLoading" class="text-center py-10">
                    <p class="text-sm font-bold text-slate-600">⏳ Memuat preview...</p>
                </div>
                <div id="pdfPreviewImages" class="space-y-3"></div>
            </div>
            <div class="flex flex-col sm:flex-row gap-2 px-5 py-4 border-t border-slate-200">
                <a id="btnDownloadPdf" href="" class="flex-1 px-4 py-2.5 bg-[#003399] hover:bg-[#002266] text-white rounded-xl text-xs font-bold text-center transition">⬇️ Download PDF</a>
                <button type="button" onclick="tutupPreviewPdf()" class="flex-1 px-4 py-2.5 bg-white border border-slate-300 text-slate-700 rounded-xl text-xs font-bold hover:bg-slate-50 transition">Tutup</button>
            </div>
        </div>
    </div>
    <script>
        async function bukaPreviewPdf() {
            const t = Date.now();
            document.getElementById('btnDownloadPdf').href = "{{ route('laporan.pdf', $kunjungan->nomor) }}?download=1&t=" + t;
            document.getElementById('modalPreviewPdf').classList.remove('hidden');
            document.body.style.overflow = 'hidden';
            const loading = document.getElementById('pdfPreviewLoading');
            const wrap = document.getElementById('pdfPreviewImages');
            loading.style.display = 'block';
            wrap.innerHTML = '';
            try {
                const res = await fetch("{{ route('laporan.preview', $kunjungan->nomor) }}?t=" + t);
                const data = await res.json();
                loading.style.display = 'none';
                if (data.images && data.images.length) {
                    data.images.forEach(src => {
                        const img = document.createElement('img');
                        img.src = src;
                        img.className = 'w-full rounded-lg shadow-md border border-slate-300';
                        img.alt = 'Preview PDF';
                        wrap.appendChild(img);
                    });
                } else if (data.pdf_url) {
                    const frame = document.createElement('iframe');
                    frame.src = data.pdf_url;
                    frame.className = 'w-full rounded-lg shadow-md border border-slate-300 bg-white';
                    frame.style.height = '60vh';
                    frame.title = 'Preview PDF';
                    wrap.appendChild(frame);
                } else {
                    wrap.innerHTML = '<p class="text-center text-sm text-rose-600 font-bold py-6">Gagal memuat preview.</p>';
                }
            } catch (e) {
                loading.style.display = 'none';
                wrap.innerHTML = '<p class="text-center text-sm text-rose-600 font-bold py-6">Gagal memuat preview.</p>';
            }
        }
        function tutupPreviewPdf() {
            document.getElementById('modalPreviewPdf').classList.add('hidden');
            document.body.style.overflow = '';
        }
    </script>

@endsection