@extends('layouts.app')

@section('title', 'Laporan & Rekapitulasi')
@section('header_title', 'Laporan Kunjungan & Berita Acara')

@section('content')
<div class="space-y-6">

    <!-- Alert Notifikasi -->
    @if(session('success'))
        <div class="p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-700 text-xs font-bold flex items-center gap-2 shadow-sm">
            <svg class="w-5 h-5 shrink-0 text-emerald-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
            {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-700 text-xs font-bold flex items-center gap-2 shadow-sm">
            <svg class="w-5 h-5 shrink-0 text-rose-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
            {{ session('error') }}
        </div>
    @endif

    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
            <h3 class="text-lg font-bold text-[#002266]">Rekapitulasi Laporan Kunjungan</h3>
            <p class="text-xs text-slate-500 font-medium mt-0.5">Pemantauan laporan pekerjaan dan persetujuan (Approval) internal.</p>
        </div>
    </div>

    <!-- FITUR BARU: Baris Filter & Pencarian -->
    <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-sm flex flex-col sm:flex-row gap-4 items-end">
        <form action="{{ route('laporan.index') }}" method="GET" class="w-full flex flex-col sm:flex-row gap-4 items-end">
            <!-- Search -->
            <div class="flex-1 w-full">
                <label for="search" class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1.5">Pencarian Data</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                        <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    </div>
                    <input type="text" id="search" name="search" value="{{ request('search') }}" placeholder="Cari No. Kunjungan, Nama Klien, atau Engineer..." class="w-full pl-9 pr-3 py-2 bg-slate-50 border border-slate-300 rounded-xl text-xs text-slate-800 font-medium focus:outline-none focus:ring-2 focus:ring-[#003399]">
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
                    <a href="{{ route('laporan.index') }}" class="w-full sm:w-auto px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-600 text-xs font-bold rounded-xl transition flex items-center justify-center text-center">
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
                        <th class="p-4 font-bold">Nomor Kunjungan</th>
                        <th class="p-4 font-bold">Customer & Engineer</th>
                        <th class="p-4 text-center font-bold">Status Dokumen</th>
                        <th class="p-4 text-center font-bold">Status Approval</th>
                        <th class="p-4 font-bold">Aksi / Catatan Atasan</th>
                        <th class="p-4 text-center font-bold">Cetak PDF</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($laporanList as $l)
                        <tr class="hover:bg-slate-50 transition-colors">
                            <td class="p-4 align-top">
                                @if($l->kunjungan)
                                    <a href="{{ route('kunjungan.show', $l->kunjungan->nomor) }}" class="font-mono text-[#003399] font-bold hover:underline">{{ $l->kunjungan->nomor }}</a>
                                @else
                                    <span class="font-mono text-slate-400 font-bold">-</span>
                                @endif
                                <p class="text-[10px] text-slate-500 mt-1 font-medium">{{ $l->kunjungan->pekerjaan ?? '-' }}</p>
                                <p class="text-[10px] text-slate-400 mt-0.5">{{ $l->kunjungan->tanggal ?? '-' }}</p>
                            </td>
                            
                            <td class="p-4 align-top">
                                @if($l->kunjungan && $l->kunjungan->customer)
                                    <a href="{{ route('master.customer.show', $l->kunjungan->customer->kode) }}" class="font-bold text-slate-800 mb-1 hover:text-[#003399] hover:underline">{{ $l->kunjungan->customer->nama_perusahaan }}</a>
                                @else
                                    <p class="font-bold text-slate-800 mb-1">{{ $l->kunjungan->customer->nama_perusahaan ?? '-' }}</p>
                                @endif
                                <p class="text-[10px] text-slate-500 font-medium">Eng: {{ $l->kunjungan->engineer->user->nama ?? '-' }}</p>
                            </td>
                            
                            <td class="p-4 align-top text-center">
                                @if($l->buktiPenyelesaian)
                                    <span class="inline-block px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-700 border border-emerald-200">
                                        ✔ Ditandatangani
                                    </span>
                                @else
                                    <span class="inline-block px-2.5 py-1 rounded-full text-[10px] font-bold bg-slate-100 text-slate-600 border border-slate-200">
                                        Menunggu
                                    </span>
                                @endif
                            </td>

                            <td class="p-4 align-top text-center">
                                @if($l->status_approval == 'Disetujui')
                                    <span class="inline-block px-2.5 py-1 rounded-full text-[10px] font-bold bg-blue-100 text-blue-700 border border-blue-200">✔ Disetujui</span>
                                @elseif($l->status_approval == 'Revisi')
                                    <span class="inline-block px-2.5 py-1 rounded-full text-[10px] font-bold bg-amber-100 text-amber-700 border border-amber-200">✍ Revisi</span>
                                @elseif($l->status_approval == 'Ditolak')
                                    <span class="inline-block px-2.5 py-1 rounded-full text-[10px] font-bold bg-rose-100 text-rose-700 border border-rose-200">✖ Ditolak</span>
                                @else
                                    <span class="inline-block px-2.5 py-1 rounded-full text-[10px] font-bold bg-slate-100 text-slate-600 border border-slate-200">⌛ Menunggu</span>
                                @endif
                            </td>

                            <!-- Form Approval Atasan / Aksi Revisi Engineer -->
                            <td class="p-4 min-w-[240px] align-top">
                                @if(auth()->user()->id_role != 3) 
                                    <!-- Jika Login Sebagai Atasan (Kepala / Pimpinan) -->
                                    <form action="{{ route('laporan.approve', $l->id_laporan) }}" method="POST" class="flex flex-col gap-2">
                                        @csrf
                                        <div class="flex gap-2">
                                            <select name="status_approval" class="bg-white border border-slate-300 text-xs font-medium rounded-lg p-1.5 text-slate-700 outline-none focus:border-[#003399] focus:ring-1 focus:ring-[#003399] w-full transition">
                                                <option value="Menunggu Persetujuan" {{ $l->status_approval == 'Menunggu Persetujuan' ? 'selected' : '' }}>⌛ Menunggu</option>
                                                <option value="Disetujui" {{ $l->status_approval == 'Disetujui' ? 'selected' : '' }}>✔ Disetujui</option>
                                                <option value="Revisi" {{ $l->status_approval == 'Revisi' ? 'selected' : '' }}>✍ Revisi</option>
                                                <option value="Ditolak" {{ $l->status_approval == 'Ditolak' ? 'selected' : '' }}>✖ Ditolak</option>
                                            </select>
                                            <button type="submit" class="bg-[#002266] hover:bg-[#001233] text-white text-xs px-3 py-1.5 rounded-lg transition font-bold shadow-sm">
                                                Simpan
                                            </button>
                                        </div>
                                        <input type="text" name="catatan_approval" value="{{ $l->catatan_approval }}" placeholder="Tambahkan catatan jika direvisi..." class="bg-white border border-slate-300 text-xs font-medium rounded-lg p-2 text-slate-700 w-full outline-none focus:border-[#003399] focus:ring-1 focus:ring-[#003399] transition">
                                    </form>
                                @else 
                                    <!-- Jika Login Sebagai Engineer -->
                                    <div class="bg-slate-50 rounded-lg p-3 border border-slate-200 mb-2">
                                        <p class="text-xs text-slate-600 font-medium italic">
                                            {{ $l->catatan_approval ? '"'.$l->catatan_approval.'"' : 'Tidak ada catatan dari atasan.' }}
                                        </p>
                                    </div>
                                    
                                    <!-- Tombol Muncul Jika Status Revisi / Ditolak -->
                                    @if($l->status_approval == 'Revisi' || $l->status_approval == 'Ditolak')
                                        <button onclick="document.getElementById('modalRevisi-{{ $l->id_laporan }}').classList.remove('hidden')" class="w-full bg-amber-100 hover:bg-amber-200 text-amber-700 border border-amber-300 text-xs px-3 py-2 rounded-lg transition font-bold shadow-sm flex justify-center items-center gap-2">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                            Perbaiki Laporan
                                        </button>

                                        <!-- Modal Revisi Laporan -->
                                        <div id="modalRevisi-{{ $l->id_laporan }}" class="hidden fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-[60] flex items-center justify-center p-4">
                                            <div class="bg-white border border-slate-200 rounded-2xl w-full max-w-md p-6 shadow-2xl">
                                                <div class="flex justify-between items-center mb-4">
                                                    <h4 class="text-base font-bold text-[#002266]">Perbaiki Deskripsi Laporan</h4>
                                                    <button type="button" onclick="document.getElementById('modalRevisi-{{ $l->id_laporan }}').classList.add('hidden')" class="text-slate-400 hover:text-rose-500 transition text-lg">&times;</button>
                                                </div>
                                                <form action="{{ route('laporan.revisi', $l->id_laporan) }}" method="POST" class="space-y-4 text-xs font-medium">
                                                    @csrf
                                                    <div>
                                                        <label class="block text-slate-700 font-bold mb-1.5">Catatan/Koreksi dari Atasan:</label>
                                                        <div class="p-3 bg-amber-50 border border-amber-200 text-amber-700 rounded-xl italic">
                                                            "{{ $l->catatan_approval }}"
                                                        </div>
                                                    </div>
                                                    <div>
                                                        <label class="block text-slate-700 font-bold mb-1.5">Deskripsi Pekerjaan (Bisa Diedit):</label>
                                                        <textarea name="catatan_revisi" rows="5" required class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl text-slate-800 focus:outline-none focus:ring-2 focus:ring-[#003399]">{{ $l->kunjungan->aktivitas->last()->catatan ?? '' }}</textarea>
                                                        <p class="text-[10px] text-slate-500 mt-1.5">Sesuaikan deskripsi di atas sesuai arahan revisi. Jika diajukan, status akan otomatis kembali menjadi "Menunggu Persetujuan".</p>
                                                    </div>
                                                    <div class="flex justify-end gap-2 pt-4">
                                                        <button type="button" onclick="document.getElementById('modalRevisi-{{ $l->id_laporan }}').classList.add('hidden')" class="px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-xl transition">Batal</button>
                                                        <button type="submit" class="px-4 py-2.5 bg-[#0044cc] hover:bg-[#003399] text-white font-bold rounded-xl shadow-lg shadow-blue-600/20 transition">Ajukan Ulang Laporan</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    @endif
                                @endif
                            </td>

                            <!-- Cetak PDF & Kirim Email -->
                            <td class="p-4 align-top text-center space-y-2 min-w-[120px]">
                                <a href="{{ route('laporan.pdf', $l->kunjungan->nomor) }}" target="_blank" 
                                   class="flex items-center justify-center gap-1.5 px-3 py-2 bg-white border border-rose-200 hover:bg-rose-50 text-rose-600 rounded-lg text-[10px] font-bold transition w-full shadow-sm">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                    <span>Cetak PDF</span>
                                </a>
                                
                                @if($l->status_approval == 'Disetujui')
                                    <form action="{{ route('laporan.email', $l->kunjungan->nomor) }}" method="POST">
                                        @csrf
                                        <button type="button" onclick="showConfirmModal(this.form, 'Kirim Email ke Klien', 'Apakah Anda yakin ingin mengirim Berita Acara ini ke email klien?')" class="flex items-center justify-center gap-1.5 px-3 py-2 bg-[#002266] hover:bg-[#001233] text-white rounded-lg text-[10px] font-bold transition w-full shadow-sm">
                                        <span>📧 Kirim Klien</span>
                                        </button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="p-6 text-center text-slate-400 font-medium italic">Belum ada laporan kunjungan yang diterbitkan.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-4 border-t border-slate-200 bg-slate-50 rounded-b-2xl">
            {{ $laporanList->links() }}
        </div>
    </div>

</div>
@endsection