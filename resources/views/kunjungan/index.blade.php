@extends('layouts.app')

@section('title', 'Kunjungan Kerja')
@section('header_title', 'Manajemen Kunjungan Kerja')

@section('content')
<div class="space-y-6">

    @if(session('success'))
        <div class="p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-700 text-xs flex items-center gap-2 font-bold shadow-sm">
            <svg class="w-5 h-5 shrink-0 text-emerald-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    <!-- Header Section -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
            <h3 class="text-lg font-bold text-[#002266]">Daftar Kunjungan Engineer</h3>
            <p class="text-xs text-slate-500 font-medium mt-0.5">Monitoring seluruh siklus kunjungan dari penugasan hingga verifikasi</p>
        </div>
        @if(Auth::user()->id_role == 2)
            <button onclick="document.getElementById('modalTambahKunjungan').classList.remove('hidden')" 
                    class="px-4 py-2.5 bg-[#002266] hover:bg-[#001233] text-white rounded-xl text-xs font-bold shadow-lg shadow-blue-900/20 transition flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Buat Jadwal Kunjungan
            </button>
        @endif
    </div>

    <!-- Baris Filter & Pencarian -->
    <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-sm flex flex-col sm:flex-row gap-4 items-end">
        <form action="{{ route('kunjungan.index') }}" method="GET" class="w-full flex flex-col sm:flex-row gap-4 items-end">
            <!-- Search -->
            <div class="flex-1 w-full">
                <label for="search" class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1.5">Pencarian Data</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                        <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    </div>
                    <input type="text" id="search" name="search" value="{{ request('search') }}" placeholder="Cari No. Kunjungan, Klien, atau Pekerjaan..." class="w-full pl-9 pr-3 py-2 bg-slate-50 border border-slate-300 rounded-xl text-xs text-slate-800 font-medium focus:outline-none focus:ring-2 focus:ring-[#003399]">
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
                    <a href="{{ route('kunjungan.index') }}" class="w-full sm:w-auto px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-600 text-xs font-bold rounded-xl transition flex items-center justify-center text-center">
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
                        <th class="p-4 font-bold">Nomor & Tanggal</th>
                        <th class="p-4 font-bold">Customer & Lokasi</th>
                        <th class="p-4 font-bold">Pekerjaan</th>
                        <th class="p-4 font-bold">Engineer</th>
                        <th class="p-4 font-bold">Status</th>
                        <th class="p-4 text-center font-bold">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($kunjunganList as $k)
                        <tr class="hover:bg-slate-50 transition-colors">
                            <td class="p-4 align-top">
                                <span class="font-mono text-[#003399] font-bold">{{ $k->nomor }}</span>
                                <p class="text-[10px] text-slate-500 mt-0.5 font-medium">{{ $k->tanggal }} ({{$k->waktu }})</p>
                            </td>
                            <td class="p-4 align-top">
                                <p class="font-bold text-slate-800">
                                    @if($k->customer)
                                        <a href="{{ route('master.customer.show', $k->customer->kode) }}" class="hover:text-[#003399] hover:underline">{{ $k->customer->nama_perusahaan }}</a>
                                    @else
                                        -
                                    @endif
                                </p>
                                <!-- Indikator Cabang (Jika Ada) -->
                                @if(isset($k->site) &&$k->site)
                                    <span class="inline-block px-1.5 py-0.5 bg-blue-100 text-[#003399] text-[9px] font-bold rounded mt-1 border border-blue-200">
                                        📍 Cabang: {{ $k->site->nama_cabang }}
                                    </span>
                                @endif
                                <p class="text-[10px] text-slate-500 truncate max-w-xs mt-0.5" title="{{ $k->alamat_sinkron }}">{{ $k->alamat_sinkron }}</p>
                                @if($k->patokan)
                                    <p class="text-[10px] text-amber-600 font-semibold truncate max-w-xs" title="{{ $k->patokan }}">📎 {{ $k->patokan }}</p>
                                @endif
                            </td>
                            <td class="p-4 font-medium text-slate-700 align-top">{{ $k->pekerjaan }}</td>
                            <td class="p-4 align-top">
                                <span class="font-medium text-slate-700">{{ $k->engineer->user->nama ?? 'Belum Ditugaskan' }}</span>
                            </td>
                            <td class="p-4 align-top">
                                <span class="inline-block px-2.5 py-1 rounded-full text-[10px] font-bold {{ $k->status == 'Selesai' ? 'bg-emerald-100 text-emerald-700 border-emerald-200' : ($k->status == 'Dikerjakan' ? 'bg-blue-100 text-blue-700 border-blue-200' : ($k->status == 'Reschedule' ? 'bg-rose-100 text-rose-700 border-rose-200' : 'bg-amber-100 text-amber-700 border-amber-200')) }}">
                                    {{ $k->status }}
                                </span>
                                @if($k->status == 'Reschedule')
                                    <p class="text-[9px] text-rose-500 mt-1.5 font-medium max-w-[120px] truncate" title="{{ $k->alasan_reschedule }}">Alasan: {{ $k->alasan_reschedule }}</p>
                                @endif
                            </td>
                            <td class="p-4 align-top text-center flex flex-col items-center gap-2">
                                <a href="{{ route('kunjungan.show', $k->nomor) }}" class="w-full px-3 py-1.5 bg-blue-50 border border-blue-100 hover:bg-[#003399] text-[#003399] hover:text-white rounded-lg text-[10px] font-bold transition">
                                    Detail
                                </a>
                                
                                @if(Auth::user()->id_role == 2 && $k->status != 'Selesai')
                                    <button onclick="document.getElementById('modalEditKunjungan-{{ $k->id_kunjungan }}').classList.remove('hidden')" class="w-full px-3 py-1.5 bg-amber-50 border border-amber-100 hover:bg-amber-500 text-amber-600 hover:text-white rounded-lg text-[10px] font-bold transition">
                                        Edit / Reschedule
                                    </button>
                                    
                                    <form id="formDeleteKunjungan-{{ $k->id_kunjungan }}" action="{{ route('kunjungan.destroy', $k->nomor) }}" method="POST" class="w-full">
                                        @csrf
                                        @method('DELETE')
                                        <button type="button" onclick="showConfirmModal(document.getElementById('formDeleteKunjungan-{{ $k->id_kunjungan }}'), 'Hapus Jadwal', 'Apakah Anda yakin ingin menghapus jadwal kunjungan ini secara permanen? Data yang sudah dihapus tidak dapat dikembalikan.')" class="w-full px-3 py-1.5 bg-rose-50 border border-rose-100 hover:bg-rose-600 text-rose-600 hover:text-white rounded-lg text-[10px] font-bold transition">
                                            Hapus
                                        </button>
                                    </form>
                                @endif
                            </td>
                        </tr>

                        <!-- Modal Edit Kunjungan -->
                        @if(Auth::user()->id_role == 2 && $k->status != 'Selesai')
                        <div id="modalEditKunjungan-{{ $k->id_kunjungan }}" class="hidden fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 flex items-center justify-center p-4 text-left">
                            <div class="bg-white border border-slate-200 rounded-2xl w-full max-w-lg p-6 shadow-2xl max-h-[90vh] overflow-y-auto">
                                <div class="flex justify-between items-center mb-4">
                                    <h4 class="text-base font-bold text-[#002266]">Edit Jadwal: {{ $k->nomor }}</h4>
                                    <button onclick="document.getElementById('modalEditKunjungan-{{ $k->id_kunjungan }}').classList.add('hidden')" class="text-slate-400 hover:text-rose-500 transition text-lg">&times;</button>
                                </div>
                                <form action="{{ route('kunjungan.update', $k->nomor) }}" method="POST" class="space-y-4 text-xs">
                                    @csrf
                                    @method('PUT')
                                    <div>
                                        <label class="block text-slate-700 font-semibold mb-1.5">Customer / Klien</label>
                                        <select name="id_customer" data-target-site="id_site_edit_{{ $k->id_kunjungan }}" required class="customer-select-edit w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl text-slate-800 focus:outline-none focus:ring-2 focus:ring-[#003399]">
                                            @foreach($customers as $c)
                                                <option value="{{ $c->id_customer }}" data-kode="{{ $c->kode }}" data-alamat="{{ $c->alamat }}" {{ $k->id_customer == $c->id_customer ? 'selected' : '' }}>{{ $c->nama_perusahaan }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <!-- FITUR BARU: Dropdown Cabang -->
                                    <div>
                                        <label class="block text-slate-700 font-semibold mb-1.5">Cabang / Lokasi Site</label>
                                        <select name="id_site" id="id_site_edit_{{ $k->id_kunjungan }}" data-selected="{{ $k->id_site ?? '' }}" class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl text-slate-800 focus:outline-none focus:ring-2 focus:ring-[#003399]">
                                            <option value="">Pusat / Tanpa Cabang</option>
                                            <!-- Pilihan terisi otomatis oleh AJAX -->
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-slate-700 font-semibold mb-1.5">Tugaskan Engineer</label>
                                        <select name="id_engineer" class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl text-slate-800 focus:outline-none focus:ring-2 focus:ring-[#003399]">
                                            <option value="">Pilih Engineer</option>
                                            @foreach($engineers as $e)
                                                <option value="{{ $e->id_engineer }}" {{ $k->id_engineer == $e->id_engineer ? 'selected' : '' }}>{{ $e->user->nama ?? '-' }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-slate-700 font-semibold mb-1.5">Tim Support (Maks. 4 Orang)</label>
                                        <div class="grid grid-cols-2 gap-2 bg-slate-50 p-3 rounded-xl border border-slate-200 max-h-32 overflow-y-auto">
                                            @php $selectedSupport =$k->supportEngineers->pluck('id_engineer')->toArray(); @endphp
                                            @foreach($engineers as $e)
                                                <label class="flex items-center gap-2 cursor-pointer text-slate-700 font-medium">
                                                    <input type="checkbox" name="support_engineers[]" value="{{ $e->id_engineer }}" {{ in_array($e->id_engineer,$selectedSupport) ? 'checked' : '' }} class="rounded border-slate-300 text-[#003399] focus:ring-[#003399]">
                                                    <span class="text-[11px] truncate">{{ $e->user->nama ?? '-' }}</span>
                                                </label>
                                            @endforeach
                                        </div>
                                    </div>
                                    <div class="grid grid-cols-2 gap-3">
                                        <div>
                                            <label class="block text-slate-700 font-semibold mb-1.5">Tanggal</label>
                                            <input type="date" name="tanggal" value="{{ $k->tanggal }}" required class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl text-slate-800 focus:outline-none focus:ring-2 focus:ring-[#003399]">
                                        </div>
                                        <div>
                                            <label class="block text-slate-700 font-semibold mb-1.5">Waktu</label>
                                            <input type="time" name="waktu" value="{{ $k->waktu }}" required class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl text-slate-800 focus:outline-none focus:ring-2 focus:ring-[#003399]">
                                        </div>
                                    </div>
                                    <div>
                                        <label class="block text-slate-700 font-semibold mb-1.5">Deskripsi Pekerjaan</label>
                                        <input type="text" name="pekerjaan" value="{{ $k->pekerjaan }}" required class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl text-slate-800 focus:outline-none focus:ring-2 focus:ring-[#003399]">
                                    </div>
                                    <div>
                                        <label class="block text-slate-700 font-semibold mb-1.5">Alamat Kunjungan 🔒</label>
                                        <textarea rows="2" readonly placeholder="Otomatis terisi dari alamat site/customer..." class="lokasi-edit w-full px-3 py-2 bg-slate-100 border border-slate-300 rounded-xl text-slate-500 focus:outline-none cursor-not-allowed">{{ $k->alamat_sinkron }}</textarea>
                                        <p class="text-[10px] text-slate-400 mt-1 font-medium">🔒 Terkunci — mengikuti alamat site/customer yang dipilih.</p>
                                    </div>
                                    <div>
                                        <label class="block text-slate-700 font-semibold mb-1.5">Patokan <span class="text-slate-400 font-normal">(opsional)</span></label>
                                        <textarea name="patokan" rows="2" placeholder="Contoh: Lantai 3, Gedung B, dekat lobby..." class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl text-slate-800 focus:outline-none focus:ring-2 focus:ring-[#003399]">{{ $k->patokan }}</textarea>
                                    </div>
                                    <div>
                                        <label class="block text-slate-700 font-semibold mb-1.5">Tools & Alat yang Dibawa</label>
                                        <div class="grid grid-cols-2 gap-2 bg-slate-50 p-3 rounded-xl border border-slate-200 max-h-32 overflow-y-auto">
                                            @php $selectedTools =$k->tools->pluck('id_tool')->toArray(); @endphp
                                            @foreach($tools as $t)
                                                <label class="flex items-center gap-2 cursor-pointer text-slate-700 font-medium">
                                                    <input type="checkbox" name="tools[]" value="{{ $t->id_tool }}" {{ in_array($t->id_tool,$selectedTools) ? 'checked' : '' }} class="rounded border-slate-300 text-[#003399] focus:ring-[#003399]">
                                                    <span class="text-[11px] truncate">{{ $t->nama_alat }}</span>
                                                </label>
                                            @endforeach
                                        </div>
                                    </div>
                                    <div class="flex justify-end gap-2 pt-4">
                                        <button type="button" onclick="document.getElementById('modalEditKunjungan-{{ $k->id_kunjungan }}').classList.add('hidden')" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-xl transition">Batal</button>
                                        <button type="submit" class="px-4 py-2 bg-amber-500 hover:bg-amber-600 text-white font-bold rounded-xl transition">Update Jadwal</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                        @endif

                    @empty
                        <tr>
                            <td colspan="6" class="p-6 text-center text-slate-400 font-medium italic">Data tidak ditemukan.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <!-- Menampilkan Pagination -->
        <div class="p-4 border-t border-slate-100 bg-slate-50 rounded-b-2xl">
            {{ $kunjunganList->links() }}
        </div>
    </div>

</div>

<!-- Modal Tambah Kunjungan -->
@if(Auth::user()->id_role == 2)
<div id="modalTambahKunjungan" class="hidden fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 flex items-center justify-center p-4">
    <div class="bg-white border border-slate-200 rounded-2xl w-full max-w-lg p-6 shadow-2xl max-h-[90vh] overflow-y-auto">
        <div class="flex justify-between items-center mb-4">
            <h4 class="text-base font-bold text-[#002266]">Buat Jadwal Kunjungan Baru</h4>
            <button onclick="document.getElementById('modalTambahKunjungan').classList.add('hidden')" class="text-slate-400 hover:text-rose-500 transition text-lg">&times;</button>
        </div>
        <form action="{{ route('kunjungan.store') }}" method="POST" class="space-y-4 text-xs">
            @csrf
            <div>
                <label class="block text-slate-700 font-semibold mb-1.5">Customer / Klien</label>
                <select name="id_customer" id="id_customer_add" required class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl text-slate-800 focus:outline-none focus:ring-2 focus:ring-[#003399]">
                    <option value="">Pilih Customer</option>
                    @foreach($customers as $c)
                        <option value="{{ $c->id_customer }}" data-kode="{{ $c->kode }}" data-alamat="{{ $c->alamat }}">{{ $c->nama_perusahaan }} ({{$c->pic }})</option>
                    @endforeach
                </select>
            </div>
            <!-- FITUR BARU: Dropdown Cabang -->
            <div>
                <label class="block text-slate-700 font-semibold mb-1.5">Cabang / Lokasi Site</label>
                <select name="id_site" id="id_site_add" class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl text-slate-800 focus:outline-none focus:ring-2 focus:ring-[#003399]" disabled>
                    <option value="">Pilih Customer Terlebih Dahulu</option>
                </select>
            </div>
            <div>
                <label class="block text-slate-700 font-semibold mb-1.5">Tugaskan Engineer</label>
                <select name="id_engineer" class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl text-slate-800 focus:outline-none focus:ring-2 focus:ring-[#003399]">
                    <option value="">Pilih Engineer (Opsional)</option>
                    @foreach($engineers as $e)
                        <option value="{{ $e->id_engineer }}">{{ $e->user->nama ?? '-' }} - ({{ $e->status_ketersediaan }})</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-slate-700 font-semibold mb-1.5">Tim Support (Maks. 4 Orang)</label>
                <div class="grid grid-cols-2 gap-2 bg-slate-50 p-3 rounded-xl border border-slate-200 max-h-32 overflow-y-auto">
                    @foreach($engineers as $e)
                        <label class="flex items-center gap-2 cursor-pointer text-slate-700 font-medium">
                            <input type="checkbox" name="support_engineers[]" value="{{ $e->id_engineer }}" class="rounded border-slate-300 text-[#003399] focus:ring-[#003399]">
                            <span class="text-[11px] truncate">{{ $e->user->nama ?? '-' }}</span>
                        </label>
                    @endforeach
                </div>
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-slate-700 font-semibold mb-1.5">Tanggal</label>
                    <input type="date" name="tanggal" required class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl text-slate-800 focus:outline-none focus:ring-2 focus:ring-[#003399]">
                </div>
                <div>
                    <label class="block text-slate-700 font-semibold mb-1.5">Waktu</label>
                    <input type="time" name="waktu" required class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl text-slate-800 focus:outline-none focus:ring-2 focus:ring-[#003399]">
                </div>
            </div>
            <div>
                <label class="block text-slate-700 font-semibold mb-1.5">Deskripsi Pekerjaan</label>
                <input type="text" name="pekerjaan" placeholder="Misal: Instalasi Router Core & Switch" required class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl text-slate-800 focus:outline-none focus:ring-2 focus:ring-[#003399]">
            </div>
            <div>
                <label class="block text-slate-700 font-semibold mb-1.5">Alamat Kunjungan 🔒</label>
                <textarea id="lokasi_add" rows="2" readonly placeholder="Otomatis terisi dari alamat site/customer..." class="w-full px-3 py-2 bg-slate-100 border border-slate-300 rounded-xl text-slate-500 focus:outline-none cursor-not-allowed"></textarea>
                <p class="text-[10px] text-slate-400 mt-1 font-medium">🔒 Terkunci — mengikuti alamat site/customer yang dipilih.</p>
            </div>
            <div>
                <label class="block text-slate-700 font-semibold mb-1.5">Patokan <span class="text-slate-400 font-normal">(opsional)</span></label>
                <textarea name="patokan" rows="2" placeholder="Contoh: Lantai 3, Gedung B, dekat lobby..." class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl text-slate-800 focus:outline-none focus:ring-2 focus:ring-[#003399]"></textarea>
            </div>
            <div>
                <label class="block text-slate-700 font-semibold mb-1.5">Tools & Alat yang Dibawa</label>
                <div class="grid grid-cols-2 gap-2 bg-slate-50 p-3 rounded-xl border border-slate-200 max-h-32 overflow-y-auto">
                    @foreach($tools as $t)
                        <label class="flex items-center gap-2 cursor-pointer text-slate-700 font-medium">
                            <input type="checkbox" name="tools[]" value="{{ $t->id_tool }}" class="rounded border-slate-300 text-[#003399] focus:ring-[#003399]">
                            <span class="text-[11px] truncate">{{ $t->nama_alat }}</span>
                        </label>
                    @endforeach
                </div>
            </div>
            <div class="flex justify-end gap-2 pt-4">
                <button type="button" onclick="document.getElementById('modalTambahKunjungan').classList.add('hidden')" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-xl transition">Batal</button>
                <button type="submit" class="px-4 py-2 bg-[#002266] hover:bg-[#001233] text-white font-bold rounded-xl shadow-lg shadow-blue-900/20 transition">Simpan & Jadwalkan</button>
            </div>
        </form>
    </div>
</div>
@endif

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // --- LOGIC ENGINEER & SUPPORT (Kode Asli) ---
        const leadSelectAdd = document.querySelector('#modalTambahKunjungan select[name="id_engineer"]');
        const supportCheckboxesAdd = document.querySelectorAll('#modalTambahKunjungan input[name="support_engineers[]"]');
        
        if(leadSelectAdd) {
            leadSelectAdd.addEventListener('change', function() {
                const selectedLeadId = this.value;
                supportCheckboxesAdd.forEach(cb => {
                    if(cb.value === selectedLeadId) {
                        cb.checked = false;
                        cb.disabled = true;
                        cb.nextElementSibling.classList.add('line-through', 'opacity-50');
                    } else {
                        cb.disabled = false;
                        cb.nextElementSibling.classList.remove('line-through', 'opacity-50');
                    }
                });
            });
        }

        const editModals = document.querySelectorAll('[id^="modalEditKunjungan-"]');
        editModals.forEach(modal => {
            const leadSelectEdit = modal.querySelector('select[name="id_engineer"]');
            const supportCheckboxesEdit = modal.querySelectorAll('input[name="support_engineers[]"]');
            
            if(leadSelectEdit) {
                const initialSelectedLeadId = leadSelectEdit.value;
                supportCheckboxesEdit.forEach(cb => {
                    if(cb.value === initialSelectedLeadId) {
                        cb.checked = false;
                        cb.disabled = true;
                        cb.nextElementSibling.classList.add('line-through', 'opacity-50');
                    }
                });

                leadSelectEdit.addEventListener('change', function() {
                    const selectedLeadId = this.value;
                    supportCheckboxesEdit.forEach(cb => {
                        if(cb.value === selectedLeadId) {
                            cb.checked = false;
                            cb.disabled = true;
                            cb.nextElementSibling.classList.add('line-through', 'opacity-50');
                        } else {
                            cb.disabled = false;
                            cb.nextElementSibling.classList.remove('line-through', 'opacity-50');
                        }
                    });
                });
            }
        });

        // --- SCRIPT AJAX FETCH CABANG/SITE + AUTO-FILL ALAMAT ---
        const escAttr = (s) => String(s ?? '').replace(/&/g, '&amp;').replace(/"/g, '&quot;').replace(/</g, '&lt;');
        const alamatOfOption = (selectEl) => {
            const opt = selectEl.options[selectEl.selectedIndex];
            return opt ? (opt.getAttribute('data-alamat') || '') : '';
        };

        // Isi textarea lokasi dari site yang dipilih; jika tanpa site, pakai alamat customer
        const syncLokasi = (siteSelectEl, lokasiEl, customerSelectEl) => {
            if (!lokasiEl) return;
            if (siteSelectEl.value) {
                const alamatSite = alamatOfOption(siteSelectEl);
                if (alamatSite) lokasiEl.value = alamatSite;
            } else if (customerSelectEl) {
                lokasiEl.value = alamatOfOption(customerSelectEl);
            }
        };

        const loadSitesAjax = (kodeCustomer, siteSelectElement, selectedSiteId = null) => {
            siteSelectElement.innerHTML = '<option value="">Memuat data...</option>';
            siteSelectElement.disabled = true;

            if(!kodeCustomer) {
                siteSelectElement.innerHTML = '<option value="">Pilih Customer Terlebih Dahulu</option>';
                return;
            }

            fetch(`/kunjungan/get-sites/${kodeCustomer}`)
                .then(res => res.json())
                .then(data => {
                    siteSelectElement.disabled = false;
                    siteSelectElement.innerHTML = '<option value="">Pusat / Tanpa Cabang</option>';
                    data.forEach(site => {
                        let isSelected = (selectedSiteId == site.id_site) ? 'selected' : '';
                        siteSelectElement.innerHTML += `<option value="${site.id_site}" data-alamat="${escAttr(site.alamat_lengkap)}" ${isSelected}>${site.nama_cabang}</option>`;
                    });
                })
                .catch(err => {
                    console.error(err);
                    siteSelectElement.innerHTML = '<option value="">Gagal memuat cabang</option>';
                });
        };

        // Trigger AJAX untuk Modal Tambah Kunjungan
        const customerAdd = document.getElementById('id_customer_add');
        const siteAdd = document.getElementById('id_site_add');
        const lokasiAdd = document.getElementById('lokasi_add');
        if(customerAdd && siteAdd) {
            customerAdd.addEventListener('change', (e) => {
                const kode = e.target.selectedOptions[0]?.dataset.kode || '';
                loadSitesAjax(kode, siteAdd);
                // Default: isi alamat customer sampai site dipilih
                if (lokasiAdd) lokasiAdd.value = alamatOfOption(customerAdd);
            });
            siteAdd.addEventListener('change', () => syncLokasi(siteAdd, lokasiAdd, customerAdd));
        }

        // Trigger AJAX untuk SEMUA Modal Edit Kunjungan
        document.querySelectorAll('.customer-select-edit').forEach(select => {
            const siteSelectId = select.getAttribute('data-target-site');
            const siteSelectElement = document.getElementById(siteSelectId);
            const preSelectedSite = siteSelectElement.getAttribute('data-selected');
            const modal = select.closest('[id^="modalEditKunjungan-"]');
            const lokasiEl = modal ? modal.querySelector('.lokasi-edit') : null;

            // Render cabang awal (saat halaman dimuat pertama kali)
            if(select.value) {
                const kodeAwal = select.selectedOptions[0]?.dataset.kode || '';
                loadSitesAjax(kodeAwal, siteSelectElement, preSelectedSite);
            }

            // Render cabang ulang jika customer diganti di tengah edit
            select.addEventListener('change', (e) => {
                const kode = e.target.selectedOptions[0]?.dataset.kode || '';
                loadSitesAjax(kode, siteSelectElement);
                if (lokasiEl) lokasiEl.value = alamatOfOption(select);
            });
            siteSelectElement.addEventListener('change', () => syncLokasi(siteSelectElement, lokasiEl, select));
        });
    });
</script>
@endsection