@extends('layouts.app')

@section('title', 'Detail Tool - ' . $tool->kode)
@section('header_title', 'Detail Tool')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <a href="{{ route('master.tool.index') }}" class="px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-xs font-bold transition">&larr; Kembali ke Daftar Tool</a>
        <a href="{{ route('peminjaman.riwayat', $tool->kode) }}" class="px-4 py-2.5 bg-white hover:bg-slate-50 text-[#002266] border border-[#002266]/30 rounded-xl text-xs font-bold shadow-sm transition">Riwayat Lengkap</a>
    </div>

    {{-- Info Tool --}}
    <div class="rounded-2xl bg-white border border-slate-200 shadow-sm p-6">
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div>
                <span class="font-mono text-xs text-[#003399] font-bold uppercase bg-blue-50 border border-blue-100 px-2.5 py-1 rounded-lg">{{ $tool->kode }}</span>
                <h3 class="text-xl font-bold text-[#002266] mt-2">{{ $tool->nama_alat }}</h3>
                <p class="text-xs text-slate-500 font-medium mt-1">{{ $tool->kategori }}</p>
            </div>
            <span class="px-3 py-1.5 rounded-full text-xs font-bold {{ $tool->status_ketersediaan == 'Tersedia' ? 'bg-blue-100 text-blue-700 border border-blue-200' : 'bg-rose-100 text-rose-700 border border-rose-200' }}">
                {{ $tool->status_ketersediaan }}
            </span>
        </div>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mt-6 text-xs">
            <div class="bg-slate-50 border border-slate-200 rounded-xl p-3">
                <p class="text-slate-500 font-bold uppercase text-[10px]">Stok Tersedia</p>
                <p class="text-lg font-bold text-emerald-600 mt-1">{{ $tool->stok }}</p>
            </div>
            <div class="bg-slate-50 border border-slate-200 rounded-xl p-3">
                <p class="text-slate-500 font-bold uppercase text-[10px]">Kategori</p>
                <p class="text-sm font-bold text-slate-800 mt-1">{{ $tool->kategori }}</p>
            </div>
            <div class="bg-slate-50 border border-slate-200 rounded-xl p-3 col-span-2">
                <p class="text-slate-500 font-bold uppercase text-[10px]">Spesifikasi</p>
                <p class="text-sm font-medium text-slate-700 mt-1">{{ $tool->spesifikasi ?? '-' }}</p>
            </div>
        </div>
        @if($tool->keterangan)
        <p class="text-xs text-slate-500 mt-4"><span class="font-bold">Keterangan:</span> {{ $tool->keterangan }}</p>
        @endif
    </div>

    {{-- Riwayat Peminjaman Terakhir --}}
    <div class="rounded-2xl bg-white border border-slate-200 overflow-hidden shadow-sm">
        <div class="px-6 py-4 border-b border-slate-200 bg-slate-50">
            <h4 class="text-sm font-bold text-[#002266]">Peminjaman Terakhir</h4>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-600">
                <thead class="text-[11px] uppercase bg-slate-50 text-slate-500 border-b border-slate-200">
                    <tr>
                        <th class="p-4 font-bold">No. Kunjungan</th>
                        <th class="p-4 font-bold">Engineer</th>
                        <th class="p-4 font-bold text-center">Jumlah</th>
                        <th class="p-4 font-bold">Dipinjam</th>
                        <th class="p-4 font-bold">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($riwayat as $r)
                        <tr class="hover:bg-slate-50 transition-colors">
                            <td class="p-4">
                                @if($r->kunjungan)
                                    <a href="{{ route('kunjungan.show', $r->kunjungan->nomor) }}" class="font-mono font-bold text-[#003399] hover:underline">{{ $r->kunjungan->nomor }}</a>
                                    <span class="block text-[11px] text-slate-500">{{ $r->kunjungan->customer->nama_perusahaan ?? '' }}</span>
                                @else
                                    <span class="italic text-slate-400">{{ $r->keterangan ?? 'Keperluan lain' }}</span>
                                @endif
                            </td>
                            <td class="p-4 font-bold text-slate-800">{{ $r->engineer->user->nama ?? '-' }}</td>
                            <td class="p-4 text-center font-bold">{{ $r->jumlah }}</td>
                            <td class="p-4 font-medium">{{ $r->tanggal_pinjam->format('d M Y, H:i') }}</td>
                            <td class="p-4">
                                <span class="px-2.5 py-1 rounded-full text-[10px] font-bold {{ $r->status == 'Dipinjam' ? 'bg-amber-100 text-amber-700 border border-amber-200' : 'bg-emerald-100 text-emerald-700 border border-emerald-200' }}">
                                    {{ $r->status }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="p-6 text-center text-slate-400">Belum ada riwayat peminjaman.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($riwayat->hasPages())
        <div class="px-6 py-4 border-t border-slate-200">{{ $riwayat->links() }}</div>
        @endif
    </div>
</div>
@endsection
