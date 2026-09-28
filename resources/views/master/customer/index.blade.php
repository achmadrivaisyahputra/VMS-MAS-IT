@extends('layouts.app')

@section('title', 'Data Customer')
@section('header_title', 'Manajemen Customer')

@section('content')
<div class="space-y-6">

    @if(session('success'))
        <div class="p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-700 text-xs font-bold flex items-center gap-2 shadow-sm">
            <svg class="w-5 h-5 shrink-0 text-emerald-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    <!-- Header Action -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
            <h3 class="text-lg font-bold text-[#002266]">Daftar Customer / Klien</h3>
            <p class="text-xs text-slate-500 font-medium mt-0.5">Kelola informasi perusahaan customer mitra MAS-IT</p>
        </div>
        <button onclick="document.getElementById('modalTambahCustomer').classList.remove('hidden')" 
                class="px-4 py-2.5 bg-[#002266] hover:bg-[#001233] text-white rounded-xl text-xs font-bold shadow-lg shadow-blue-900/20 transition flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Tambah Customer
        </button>
    </div>

    <!-- Baris Filter & Pencarian -->
    <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-sm flex flex-col sm:flex-row gap-4 items-end">
        <form action="{{ route('master.customer.index') }}" method="GET" class="w-full flex flex-col sm:flex-row gap-4 items-end">
            <!-- Search -->
            <div class="flex-1 w-full">
                <label for="search" class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1.5">Pencarian Data</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                        <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    </div>
                    <input type="text" id="search" name="search" value="{{ request('search') }}" placeholder="Cari Nama Perusahaan, PIC, atau Email..." class="w-full pl-9 pr-3 py-2 bg-slate-50 border border-slate-300 rounded-xl text-xs text-slate-800 font-medium focus:outline-none focus:ring-2 focus:ring-[#003399]">
                </div>
            </div>

            <!-- Sortir -->
            <div class="w-full sm:w-48">
                <label for="sort" class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1.5">Urutkan</label>
                <select id="sort" name="sort" class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl text-xs text-slate-800 font-medium focus:outline-none focus:ring-2 focus:ring-[#003399]">
                    <option value="terbaru" {{ request('sort') == 'terbaru' ? 'selected' : '' }}>Paling Baru (Terbaru)</option>
                    <option value="terlama" {{ request('sort') == 'terlama' ? 'selected' : '' }}>Paling Lama (Terlama)</option>
                </select>
            </div>

            <!-- Tombol Aksi -->
            <div class="flex gap-2 w-full sm:w-auto">
                <button type="submit" class="w-full sm:w-auto px-5 py-2 bg-[#002266] hover:bg-[#001233] text-white text-xs font-bold rounded-xl shadow-lg shadow-blue-900/20 transition flex items-center justify-center gap-2">
                    Terapkan
                </button>
                @if(request()->has('search') || request()->has('sort'))
                    <a href="{{ route('master.customer.index') }}" class="w-full sm:w-auto px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-600 text-xs font-bold rounded-xl transition flex items-center justify-center text-center">
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
                        <th class="p-4 font-bold">Perusahaan</th>
                        <th class="p-4 font-bold">PIC</th>
                        <th class="p-4 font-bold">Kontak / Email</th>
                        <th class="p-4 font-bold">Alamat & Kordinat</th>
                        <th class="p-4 text-center font-bold">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($customers as $c)
                        <tr class="hover:bg-slate-50 transition-colors">
                            <td class="p-4">
                                @if($c->kode)
                                <a href="{{ route('master.customer.show', $c->kode) }}" class="font-bold text-[#003399] hover:underline" title="Lihat detail customer">{{ $c->nama_perusahaan }}</a>
                                @else
                                <span class="font-bold text-slate-800">{{ $c->nama_perusahaan }}</span>
                                @endif
                                @if($c->kode)<div class="text-[10px] font-mono font-bold text-slate-400 mt-0.5"><a href="{{ route('master.customer.show', $c->kode) }}" class="hover:text-[#003399] hover:underline">{{ $c->kode }}</a></div>@endif
                            </td>
                            <td class="p-4 font-bold text-slate-800">{{ $c->pic }}</td>
                            <td class="p-4 font-medium">
                                <p class="text-slate-700">{{ $c->telepon }}</p>
                                <p class="text-[10px] text-slate-500 mt-0.5">{{ $c->email }}</p>
                            </td>
                            <td class="p-4 font-medium">
                                <p class="text-slate-700 max-w-xs truncate">{{ $c->alamat }}</p>
                                @if($c->latitude && $c->longitude)
                                    <p class="text-[10px] font-bold text-emerald-600 mt-1">GPS: {{ $c->latitude }}, {{ $c->longitude }}
                                @else
                                    <p class="text-[10px] font-bold text-rose-500 mt-1">GPS: Belum diatur</p>
                                @endif
                            </td>
                            <td class="p-4 text-center">
                                <div class="inline-flex items-center gap-2">
                                    <!-- TOMBOL BARU: Kelola Cabang -->
                                    <button onclick="openCabangModal('{{ $c->kode }}', '{{ addslashes($c->nama_perusahaan) }}')" 
                                            class="p-1.5 bg-blue-50 border border-blue-100 hover:bg-blue-600 text-blue-600 hover:text-white rounded-lg transition shadow-sm" title="Kelola Cabang/Site">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                                        </svg>
                                    </button>

                                    <!-- Tombol Edit (Bawaan) -->
                                    <button onclick="openEditModal({{ json_encode($c) }})" class="p-1.5 bg-amber-50 border border-amber-100 hover:bg-amber-500 text-amber-600 hover:text-white rounded-lg transition shadow-sm" title="Edit">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                    </button>
                                    
                                    <!-- Tombol Delete -->
                                    <form action="{{ route('master.customer.destroy', $c->kode) }}" method="POST">
                                        @csrf
                                        @method('DELETE')
                                        <button type="button" onclick="showConfirmModal(this.form, 'Hapus Customer', 'Apakah Anda yakin ingin menghapus customer {{ $c->nama_perusahaan }}?')" 
                                                class="p-1.5 bg-rose-50 border border-rose-100 hover:bg-rose-600 text-rose-600 hover:text-white rounded-lg transition shadow-sm" title="Hapus">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="p-6 text-center text-slate-400 font-medium italic">Data customer tidak ditemukan.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-4 border-t border-slate-200 bg-slate-50 rounded-b-2xl">
            {{ $customers->links() }}
        </div>
    </div>

</div>

<!-- Modal Tambah -->
<div id="modalTambahCustomer" class="hidden fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 flex items-center justify-center p-4">
    <div class="bg-white border border-slate-200 rounded-2xl w-full max-w-lg p-6 shadow-2xl">
        <div class="flex justify-between items-center mb-4">
            <h4 class="text-base font-bold text-[#002266]">Tambah Customer Baru</h4>
            <button onclick="document.getElementById('modalTambahCustomer').classList.add('hidden')" class="text-slate-400 hover:text-rose-500 transition text-lg">&times;</button>
        </div>
        <form action="{{ route('master.customer.store') }}" method="POST" class="space-y-4 text-xs font-medium">
            @csrf
            <div>
                <label class="block text-slate-700 font-bold mb-1.5">Nama Perusahaan</label>
                <input type="text" name="nama_perusahaan" required class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl text-slate-800 focus:outline-none focus:ring-2 focus:ring-[#003399]">
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-slate-700 font-bold mb-1.5">Person in Charge (PIC)</label>
                    <input type="text" name="pic" required class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl text-slate-800 focus:outline-none focus:ring-2 focus:ring-[#003399]">
                </div>
                <div>
                    <label class="block text-slate-700 font-bold mb-1.5">No. Telepon / WhatsApp</label>
                    <input type="text" name="telepon" required class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl text-slate-800 focus:outline-none focus:ring-2 focus:ring-[#003399]">
                </div>
            </div>
            <div>
                <label class="block text-slate-700 font-bold mb-1.5">Email Perusahaan / PIC</label>
                <input type="email" name="email" required class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl text-slate-800 focus:outline-none focus:ring-2 focus:ring-[#003399]">
            </div>
            <div>
                <label class="block text-slate-700 font-bold mb-1.5">Alamat Kantor</label>
                <textarea name="alamat" rows="2" required class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl text-slate-800 focus:outline-none focus:ring-2 focus:ring-[#003399]"></textarea>
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
                <button type="button" onclick="document.getElementById('modalTambahCustomer').classList.add('hidden')" class="px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-xl transition">Batal</button>
                <button type="submit" class="px-4 py-2.5 bg-[#002266] hover:bg-[#001233] text-white font-bold rounded-xl shadow-lg shadow-blue-900/20 transition">Simpan Customer</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Edit -->
<div id="modalEditCustomer" class="hidden fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 flex items-center justify-center p-4">
    <div class="bg-white border border-slate-200 rounded-2xl w-full max-w-lg p-6 shadow-2xl">
        <div class="flex justify-between items-center mb-4">
            <h4 class="text-base font-bold text-[#002266]">Edit Customer</h4>
            <button onclick="document.getElementById('modalEditCustomer').classList.add('hidden')" class="text-slate-400 hover:text-rose-500 transition text-lg">&times;</button>
        </div>
        <form id="formEditCustomer" method="POST" class="space-y-4 text-xs font-medium">
            @csrf
            @method('PUT')
            <div>
                <label class="block text-slate-700 font-bold mb-1.5">Nama Perusahaan</label>
                <input type="text" id="edit_nama_perusahaan" name="nama_perusahaan" required class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl text-slate-800 focus:outline-none focus:ring-2 focus:ring-[#003399]">
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-slate-700 font-bold mb-1.5">PIC</label>
                    <input type="text" id="edit_pic" name="pic" required class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl text-slate-800 focus:outline-none focus:ring-2 focus:ring-[#003399]">
                </div>
                <div>
                    <label class="block text-slate-700 font-bold mb-1.5">Telepon</label>
                    <input type="text" id="edit_telepon" name="telepon" required class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl text-slate-800 focus:outline-none focus:ring-2 focus:ring-[#003399]">
                </div>
            </div>
            <div>
                <label class="block text-slate-700 font-bold mb-1.5">Email</label>
                <input type="email" id="edit_email" name="email" required class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl text-slate-800 focus:outline-none focus:ring-2 focus:ring-[#003399]">
            </div>
            <div>
                <label class="block text-slate-700 font-bold mb-1.5">Alamat</label>
                <textarea id="edit_alamat" name="alamat" rows="2" required class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl text-slate-800 focus:outline-none focus:ring-2 focus:ring-[#003399]"></textarea>
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
                <button type="button" onclick="document.getElementById('modalEditCustomer').classList.add('hidden')" class="px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-xl transition">Batal</button>
                <button type="submit" class="px-4 py-2.5 bg-[#0044cc] hover:bg-[#003399] text-white font-bold rounded-xl shadow-lg shadow-blue-600/20 transition">Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Kelola Cabang -->
<div id="modalKelolaCabang" class="hidden fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 flex items-center justify-center p-4">
    <div class="bg-white border border-slate-200 rounded-2xl w-full max-w-4xl p-6 shadow-2xl flex flex-col max-h-[90vh]">
        <!-- Header -->
        <div class="flex justify-between items-center mb-4">
            <h4 class="text-base font-bold text-[#002266]">Kelola Cabang: <span id="namaPerusahaanCabang" class="text-blue-600"></span></h4>
            <button onclick="document.getElementById('modalKelolaCabang').classList.add('hidden')" class="text-slate-400 hover:text-rose-500 transition text-lg">&times;</button>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 overflow-y-auto pr-2">
            <!-- Form Tambah Cabang -->
            <div class="md:col-span-1 bg-slate-50 p-4 rounded-xl border border-slate-200 h-fit">
                <h5 class="font-bold text-slate-700 text-xs mb-3 uppercase tracking-wider">Tambah Cabang</h5>
                <form id="formTambahCabang" method="POST" class="space-y-3 text-xs font-medium">
                    @csrf
                    <div>
                        <label class="block text-slate-700 font-bold mb-1.5">Nama Cabang</label>
                        <input type="text" name="nama_cabang" required class="w-full px-3 py-2 bg-white border border-slate-300 rounded-xl text-slate-800 focus:outline-none focus:ring-2 focus:ring-[#003399]">
                    </div>
                    <div>
                        <label class="block text-slate-700 font-bold mb-1.5">Alamat Lengkap</label>
                        <textarea name="alamat_lengkap" rows="2" required class="w-full px-3 py-2 bg-white border border-slate-300 rounded-xl text-slate-800 focus:outline-none focus:ring-2 focus:ring-[#003399]"></textarea>
                    </div>
                    <div>
                        <label class="block text-slate-700 font-bold mb-1.5">Titik GPS (Optional)</label>
                        <div class="flex gap-1 mb-2">
                            <input type="text" id="site_latitude" name="latitude" placeholder="Latitude" class="w-full px-2 py-2 bg-white border border-slate-300 rounded-xl text-slate-800 focus:outline-none focus:ring-2 focus:ring-[#003399]">
                            <input type="text" id="site_longitude" name="longitude" placeholder="Longitude" class="w-full px-2 py-2 bg-white border border-slate-300 rounded-xl text-slate-800 focus:outline-none focus:ring-2 focus:ring-[#003399]">
                        </div>
                        <button type="button" onclick="getLocation('site_latitude', 'site_longitude')" class="w-full px-3 py-2 bg-emerald-500 hover:bg-emerald-600 text-white rounded-xl shadow-md transition text-[10px] font-bold">
                            📍 Auto GPS
                        </button>
                    </div>
                    <button type="submit" class="w-full px-4 py-2.5 bg-[#002266] hover:bg-[#001233] text-white font-bold rounded-xl shadow-lg transition mt-2">Simpan Cabang</button>
                </form>
            </div>

            <!-- List Data Cabang -->
            <div class="md:col-span-2">
                <h5 class="font-bold text-slate-700 text-xs mb-3 uppercase tracking-wider">Daftar Cabang Aktif</h5>
                <div class="border border-slate-200 rounded-xl overflow-hidden shadow-sm">
                    <table class="w-full text-left text-xs text-slate-600">
                        <thead class="text-[10px] uppercase bg-slate-100 text-slate-500 border-b border-slate-200">
                            <tr>
                                <th class="p-3 font-bold">Nama Cabang</th>
                                <th class="p-3 font-bold">Detail Lokasi</th>
                                <th class="p-3 text-center font-bold">Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="tbody_cabang" class="divide-y divide-slate-100 bg-white">
                            <!-- Diisi via AJAX -->
                            <tr><td colspan="3" class="p-4 text-center text-slate-400 italic">Memuat data...</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- MODAL INFO KHUSUS NOTIFIKASI GPS -->
<div id="modalInfoGPS" class="hidden fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-[60] flex items-center justify-center p-4">
    <div class="bg-white border border-slate-200 rounded-2xl w-full max-w-xs p-6 shadow-2xl text-center transition-all">
        <div id="modalInfoIcon" class="mx-auto flex items-center justify-center h-14 w-14 rounded-full mb-4">
            <!-- Ikon disuntik dari JS -->
        </div>
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

    function openEditModal(customer) {
        document.getElementById('formEditCustomer').action = `/master/customer/${customer.kode}`;
        document.getElementById('edit_nama_perusahaan').value = customer.nama_perusahaan;
        document.getElementById('edit_pic').value = customer.pic;
        document.getElementById('edit_telepon').value = customer.telepon;
        document.getElementById('edit_email').value = customer.email;
        document.getElementById('edit_alamat').value = customer.alamat;
        
        document.getElementById('edit_latitude').value = customer.latitude || '';
        document.getElementById('edit_longitude').value = customer.longitude || '';
        
        document.getElementById('modalEditCustomer').classList.remove('hidden');
    }

    function openCabangModal(kodeCustomer, namaPerusahaan) {
        document.getElementById('namaPerusahaanCabang').innerText = namaPerusahaan;
        document.getElementById('formTambahCabang').action = `/master/customer/${kodeCustomer}/site`;
        document.getElementById('modalKelolaCabang').classList.remove('hidden');
        
        const tbody = document.getElementById('tbody_cabang');
        tbody.innerHTML = '<tr><td colspan="3" class="p-4 text-center text-slate-400 italic">Memuat data cabang...</td></tr>';
        
        fetch(`/kunjungan/get-sites/${kodeCustomer}`)
            .then(res => res.json())
            .then(data => {
                tbody.innerHTML = '';
                if(data.length === 0) {
                    tbody.innerHTML = '<tr><td colspan="3" class="p-6 text-center text-slate-400 font-medium italic">Belum ada data cabang / site.</td></tr>';
                } else {
                    data.forEach(site => {
                        let gpsText = (site.latitude && site.longitude) 
                            ? `<p class="text-[10px] font-bold text-emerald-600 mt-1">GPS: ${site.latitude}, ${site.longitude}</p>` 
                            : `<p class="text-[10px] font-bold text-rose-500 mt-1">GPS: Belum diatur</p>`;
                            
                        tbody.innerHTML += `
                            <tr class="hover:bg-slate-50">
                                <td class="p-3 font-bold text-[#003399]">${site.nama_cabang}</td>
                                <td class="p-3 font-medium">
                                    <p class="text-slate-700">${site.alamat_lengkap}</p>
                                    ${gpsText}
                                </td>
                                <td class="p-3 text-center">
                                    <form action="/master/customer/site/${site.id_site}" method="POST" class="inline">
                                        <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                        <input type="hidden" name="_method" value="DELETE">
                                        <button type="button" onclick="showConfirmModal(this.form, 'Hapus Cabang', 'Yakin menghapus cabang ${site.nama_cabang}?')" 
                                                class="p-1.5 bg-rose-50 border border-rose-100 hover:bg-rose-600 text-rose-600 hover:text-white rounded-lg transition shadow-sm" title="Hapus">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        `;
                    });
                }
            })
            .catch(err => {
                console.error(err);
                tbody.innerHTML = '<tr><td colspan="3" class="p-4 text-center text-rose-500 font-bold">Gagal menarik data cabang.</td></tr>';
            });
    }
</script>
@endsection