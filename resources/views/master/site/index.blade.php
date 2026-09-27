@extends('layouts.app')

@section('title', 'Data Site')
@section('header_title', 'Manajemen Site')

@section('content')
<div class="space-y-6">

    @if(session('success'))
        <div class="p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-700 text-xs font-bold flex items-center gap-2 shadow-sm">
            <svg class="w-5 h-5 shrink-0 text-emerald-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    @if($errors->any())
        <div class="p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-700 text-xs font-bold shadow-sm">
            <ul class="list-disc list-inside space-y-1">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- Header Action -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
            <h3 class="text-lg font-bold text-[#002266]">Daftar Site Perusahaan</h3>
            <p class="text-xs text-slate-500 font-medium mt-0.5">Kelola site / cabang dari setiap customer — satu customer bisa punya banyak site</p>
        </div>
        <button onclick="document.getElementById('modalTambahSite').classList.remove('hidden')"
                class="px-4 py-2.5 bg-[#002266] hover:bg-[#001233] text-white rounded-xl text-xs font-bold shadow-lg shadow-blue-900/20 transition flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Tambah Site
        </button>
    </div>

    <!-- Baris Filter & Pencarian -->
    <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-sm">
        <form action="{{ route('master.site.index') }}" method="GET" class="w-full flex flex-col lg:flex-row gap-4 items-end">
            <!-- Search -->
            <div class="flex-1 w-full">
                <label for="search" class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1.5">Pencarian Data</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                        <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    </div>
                    <input type="text" id="search" name="search" value="{{ request('search') }}" placeholder="Cari Nama Site, Alamat, atau Perusahaan..." class="w-full pl-9 pr-3 py-2 bg-slate-50 border border-slate-300 rounded-xl text-xs text-slate-800 font-medium focus:outline-none focus:ring-2 focus:ring-[#003399]">
                </div>
            </div>

            <!-- Filter Customer -->
            <div class="w-full lg:w-56">
                <label for="id_customer" class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1.5">Filter Customer</label>
                <select id="id_customer" name="id_customer" class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl text-xs text-slate-800 font-medium focus:outline-none focus:ring-2 focus:ring-[#003399]">
                    <option value="">Semua Customer</option>
                    @foreach($customers as $cust)
                        <option value="{{ $cust->id_customer }}" {{ request('id_customer') == $cust->id_customer ? 'selected' : '' }}>{{ $cust->nama_perusahaan }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Sortir -->
            <div class="w-full lg:w-44">
                <label for="sort" class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1.5">Urutkan</label>
                <select id="sort" name="sort" class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl text-xs text-slate-800 font-medium focus:outline-none focus:ring-2 focus:ring-[#003399]">
                    <option value="terbaru" {{ request('sort') == 'terbaru' ? 'selected' : '' }}>Paling Baru (Terbaru)</option>
                    <option value="terlama" {{ request('sort') == 'terlama' ? 'selected' : '' }}>Paling Lama (Terlama)</option>
                </select>
            </div>

            <!-- Tombol Aksi -->
            <div class="flex gap-2 w-full lg:w-auto">
                <button type="submit" class="w-full lg:w-auto px-5 py-2 bg-[#002266] hover:bg-[#001233] text-white text-xs font-bold rounded-xl shadow-lg shadow-blue-900/20 transition flex items-center justify-center gap-2">
                    Terapkan
                </button>
                @if(request()->has('search') || request()->has('sort') || request()->has('id_customer'))
                    <a href="{{ route('master.site.index') }}" class="w-full lg:w-auto px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-600 text-xs font-bold rounded-xl transition flex items-center justify-center text-center">
                        Reset
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Table Card -->
    <div class="rounded-2xl bg-white border border-slate-200 overflow-hidden shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-600">
                <thead class="text-[11px] uppercase bg-slate-50 text-slate-500 border-b border-slate-200">
                    <tr>
                        <th class="p-4 font-bold">Customer</th>
                        <th class="p-4 font-bold">Nama Site</th>
                        <th class="p-4 font-bold">Alamat & Koordinat</th>
                        <th class="p-4 text-center font-bold">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($sites as $s)
                        <tr class="hover:bg-slate-50 transition-colors">
                            <td class="p-4">
                                <p class="font-bold text-[#003399]">{{ $s->customer->nama_perusahaan ?? '-' }}</p>
                                <p class="text-[10px] text-slate-500 mt-0.5">{{ $s->customer->pic ?? '' }}</p>
                            </td>
                            <td class="p-4 font-bold text-slate-800">{{ $s->nama_cabang }}</td>
                            <td class="p-4 font-medium">
                                <p class="text-slate-700 max-w-xs truncate">{{ $s->alamat_lengkap }}</p>
                                @if($s->latitude && $s->longitude)
                                    <p class="text-[10px] font-bold text-emerald-600 mt-1">GPS: {{ $s->latitude }}, {{ $s->longitude }}</p>
                                @else
                                    <p class="text-[10px] font-bold text-rose-500 mt-1">GPS: Belum diatur</p>
                                @endif
                            </td>
                            <td class="p-4 text-center">
                                <div class="inline-flex items-center gap-2">
                                    <button onclick="openEditModal({{ json_encode($s) }})" class="p-1.5 bg-amber-50 border border-amber-100 hover:bg-amber-500 text-amber-600 hover:text-white rounded-lg transition shadow-sm" title="Edit">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                    </button>
                                    <form action="{{ route('master.site.destroy', $s->id_site) }}" method="POST">
                                        @csrf
                                        @method('DELETE')
                                        <button type="button" onclick="showConfirmModal(this.form, 'Hapus Site', 'Apakah Anda yakin ingin menghapus site {{ $s->nama_cabang }}?')"
                                                class="p-1.5 bg-rose-50 border border-rose-100 hover:bg-rose-600 text-rose-600 hover:text-white rounded-lg transition shadow-sm" title="Hapus">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="p-6 text-center text-slate-400 font-medium italic">Data site tidak ditemukan.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-4 border-t border-slate-200 bg-slate-50 rounded-b-2xl">
            {{ $sites->links() }}
        </div>
    </div>

</div>

<!-- Modal Tambah -->
<div id="modalTambahSite" class="hidden fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 flex items-center justify-center p-4">
    <div class="bg-white border border-slate-200 rounded-2xl w-full max-w-lg p-6 shadow-2xl">
        <div class="flex justify-between items-center mb-4">
            <h4 class="text-base font-bold text-[#002266]">Tambah Site Baru</h4>
            <button onclick="document.getElementById('modalTambahSite').classList.add('hidden')" class="text-slate-400 hover:text-rose-500 transition text-lg">&times;</button>
        </div>
        <form action="{{ route('master.site.store') }}" method="POST" class="space-y-4 text-xs font-medium">
            @csrf
            <div>
                <label class="block text-slate-700 font-bold mb-1.5">Customer / Perusahaan Induk</label>
                <select name="id_customer" required class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl text-slate-800 focus:outline-none focus:ring-2 focus:ring-[#003399]">
                    <option value="">-- Pilih Customer --</option>
                    @foreach($customers as $cust)
                        <option value="{{ $cust->id_customer }}">{{ $cust->nama_perusahaan }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-slate-700 font-bold mb-1.5">Nama Site / Cabang</label>
                <input type="text" name="nama_cabang" required placeholder="Contoh: Cabang Depok" class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl text-slate-800 focus:outline-none focus:ring-2 focus:ring-[#003399]">
            </div>
            <div>
                <label class="block text-slate-700 font-bold mb-1.5">Alamat Lengkap Site</label>
                <textarea name="alamat_lengkap" rows="2" required class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl text-slate-800 focus:outline-none focus:ring-2 focus:ring-[#003399]"></textarea>
            </div>
            <div>
                <label class="block text-slate-700 font-bold mb-1.5">Titik Koordinat (Bisa Paste dari Google Maps)</label>
                <div class="flex gap-2">
                    <input type="text" id="add_latitude" name="latitude" placeholder="Contoh: -6.1206733" class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl text-slate-800 focus:outline-none focus:ring-2 focus:ring-[#003399]">
                    <input type="text" id="add_longitude" name="longitude" placeholder="Contoh: 106.880495" class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl text-slate-800 focus:outline-none focus:ring-2 focus:ring-[#003399]">
                    <button type="button" onclick="getLocation('add_latitude', 'add_longitude')" class="px-3 py-2 bg-emerald-500 hover:bg-emerald-600 text-white rounded-xl shadow-md transition text-[10px] font-bold whitespace-nowrap">
                        📍 Auto GPS
                    </button>
                </div>
            </div>
            <div class="flex justify-end gap-2 pt-4">
                <button type="button" onclick="document.getElementById('modalTambahSite').classList.add('hidden')" class="px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-xl transition">Batal</button>
                <button type="submit" class="px-4 py-2.5 bg-[#002266] hover:bg-[#001233] text-white font-bold rounded-xl shadow-lg shadow-blue-900/20 transition">Simpan Site</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Edit -->
<div id="modalEditSite" class="hidden fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 flex items-center justify-center p-4">
    <div class="bg-white border border-slate-200 rounded-2xl w-full max-w-lg p-6 shadow-2xl">
        <div class="flex justify-between items-center mb-4">
            <h4 class="text-base font-bold text-[#002266]">Edit Site</h4>
            <button onclick="document.getElementById('modalEditSite').classList.add('hidden')" class="text-slate-400 hover:text-rose-500 transition text-lg">&times;</button>
        </div>
        <form id="formEditSite" method="POST" class="space-y-4 text-xs font-medium">
            @csrf
            @method('PUT')
            <div>
                <label class="block text-slate-700 font-bold mb-1.5">Customer / Perusahaan Induk</label>
                <select id="edit_id_customer" name="id_customer" required class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl text-slate-800 focus:outline-none focus:ring-2 focus:ring-[#003399]">
                    @foreach($customers as $cust)
                        <option value="{{ $cust->id_customer }}">{{ $cust->nama_perusahaan }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-slate-700 font-bold mb-1.5">Nama Site / Cabang</label>
                <input type="text" id="edit_nama_cabang" name="nama_cabang" required class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl text-slate-800 focus:outline-none focus:ring-2 focus:ring-[#003399]">
            </div>
            <div>
                <label class="block text-slate-700 font-bold mb-1.5">Alamat Lengkap Site</label>
                <textarea id="edit_alamat_lengkap" name="alamat_lengkap" rows="2" required class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl text-slate-800 focus:outline-none focus:ring-2 focus:ring-[#003399]"></textarea>
            </div>
            <div>
                <label class="block text-slate-700 font-bold mb-1.5">Titik Koordinat (Bisa Paste dari Google Maps)</label>
                <div class="flex gap-2">
                    <input type="text" id="edit_latitude" name="latitude" placeholder="Contoh: -6.1206733" class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl text-slate-800 focus:outline-none focus:ring-2 focus:ring-[#003399]">
                    <input type="text" id="edit_longitude" name="longitude" placeholder="Contoh: 106.880495" class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl text-slate-800 focus:outline-none focus:ring-2 focus:ring-[#003399]">
                    <button type="button" onclick="getLocation('edit_latitude', 'edit_longitude')" class="px-3 py-2 bg-emerald-500 hover:bg-emerald-600 text-white rounded-xl shadow-md transition text-[10px] font-bold whitespace-nowrap">
                        📍 Auto GPS
                    </button>
                </div>
            </div>
            <div class="flex justify-end gap-2 pt-4">
                <button type="button" onclick="document.getElementById('modalEditSite').classList.add('hidden')" class="px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-xl transition">Batal</button>
                <button type="submit" class="px-4 py-2.5 bg-[#0044cc] hover:bg-[#003399] text-white font-bold rounded-xl shadow-lg shadow-blue-600/20 transition">Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL INFO KHUSUS NOTIFIKASI GPS -->
<div id="modalInfoGPS" class="hidden fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-[60] flex items-center justify-center p-4">
    <div class="bg-white border border-slate-200 rounded-2xl w-full max-w-xs p-6 shadow-2xl text-center transition-all">
        <div id="modalInfoIcon" class="mx-auto flex items-center justify-center h-14 w-14 rounded-full mb-4"></div>
        <h3 id="modalInfoTitle" class="text-base font-bold text-[#002266] mb-2"></h3>
        <p id="modalInfoMessage" class="text-xs text-slate-600 mb-6 font-medium leading-relaxed"></p>
        <button type="button" onclick="document.getElementById('modalInfoGPS').classList.add('hidden')" class="w-full px-4 py-2.5 bg-[#002266] hover:bg-[#001233] text-white font-bold rounded-xl text-xs transition">
            Tutup
        </button>
    </div>
</div>

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

    function getLocation(latId, longId) {
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(
                (position) => {
                    document.getElementById(latId).value = position.coords.latitude;
                    document.getElementById(longId).value = position.coords.longitude;
                    showGPSModal('Lokasi Ditemukan!', 'Titik koordinat GPS Anda berhasil didapatkan dan dimasukkan ke dalam form.', true);
                },
                (error) => {
                    showGPSModal('Gagal Membaca GPS', 'Pastikan GPS aktif dan izin lokasi (Location) pada browser Anda telah diizinkan.', false);
                },
                { enableHighAccuracy: true }
            );
        } else {
            showGPSModal('Tidak Mendukung', 'Browser atau perangkat Anda tidak mendukung fitur Geolocation.', false);
        }
    }

    function openEditModal(site) {
        document.getElementById('formEditSite').action = `/master/site/${site.id_site}`;
        document.getElementById('edit_id_customer').value = site.id_customer;
        document.getElementById('edit_nama_cabang').value = site.nama_cabang;
        document.getElementById('edit_alamat_lengkap').value = site.alamat_lengkap;
        document.getElementById('edit_latitude').value = site.latitude || '';
        document.getElementById('edit_longitude').value = site.longitude || '';

        document.getElementById('modalEditSite').classList.remove('hidden');
    }
</script>
@endsection
