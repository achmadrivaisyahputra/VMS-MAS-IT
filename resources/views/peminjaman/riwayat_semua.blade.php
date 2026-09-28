@extends('layouts.app')

@section('title', 'Riwayat Peminjaman Tools')
@section('header_title', 'Riwayat Peminjaman Tools')

@section('content')
<div class="space-y-6">
    <div>
        <h3 class="text-lg font-bold text-[#002266]">Semua Riwayat Peminjaman</h3>
        <p class="text-xs text-slate-500 font-medium mt-0.5">Pantau siapa meminjam apa, kapan, dan status pengembaliannya</p>
    </div>

    <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-sm">
        <form action="{{ route('master.peminjaman.riwayat-semua') }}" method="GET" class="flex flex-col sm:flex-row gap-4 items-end">
            <div class="flex-1 w-full">
                <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1.5">Pencarian</label>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama tool, kode, atau engineer..." class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl text-xs text-slate-800 font-medium focus:outline-none focus:ring-2 focus:ring-[#003399]">
            </div>
            <div class="w-full sm:w-48">
                <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1.5">Status</label>
                <select name="status" class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl text-xs text-slate-800 font-medium focus:outline-none focus:ring-2 focus:ring-[#003399]">
                    <option value="">Semua Status</option>
                    <option value="Dipinjam" {{ request('status') == 'Dipinjam' ? 'selected' : '' }}>Dipinjam</option>
                    <option value="Dikembalikan" {{ request('status') == 'Dikembalikan' ? 'selected' : '' }}>Dikembalikan</option>
                </select>
            </div>
            <div class="flex gap-2 w-full sm:w-auto">
                <button type="submit" class="px-5 py-2 bg-[#002266] hover:bg-[#001233] text-white text-xs font-bold rounded-xl shadow-lg shadow-blue-900/20 transition">Terapkan</button>
                @if(request('search') || request('status'))
                    <a href="{{ route('master.peminjaman.riwayat-semua') }}" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-600 text-xs font-bold rounded-xl transition">Reset</a>
                @endif
            </div>
        </form>
    </div>

    <div class="rounded-2xl bg-white border border-slate-200 overflow-hidden shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-600">
                <thead class="text-[11px] uppercase bg-slate-50 text-slate-500 border-b border-slate-200">
                    <tr>
                        <th class="p-4 font-bold">Tool</th>
                        <th class="p-4 font-bold">Engineer</th>
                        <th class="p-4 font-bold text-center">Jumlah</th>
                        <th class="p-4 font-bold">Dipinjam</th>
                        <th class="p-4 font-bold">Dikembalikan</th>
                        <th class="p-4 font-bold">Kondisi</th>
                        <th class="p-4 font-bold">Keperluan</th>
                        <th class="p-4 font-bold">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($riwayat as $r)
                        <tr class="hover:bg-slate-50 transition-colors">
                            <td class="p-4">
                                <span class="font-mono text-[10px] text-[#003399] font-bold uppercase">{{ $r->tool->kode ?? '-' }}</span>
                                <p class="font-bold text-slate-800 mt-1">{{ $r->tool->nama_alat ?? '-' }}</p>
                            </td>
                            <td class="p-4 font-bold text-slate-800">{{ $r->engineer->user->nama ?? '-' }}</td>
                            <td class="p-4 text-center font-bold">{{ $r->jumlah }}</td>
                            <td class="p-4 font-medium">{{ $r->tanggal_pinjam->format('d M Y, H:i') }}</td>
                            <td class="p-4 font-medium">{{ $r->tanggal_kembali?->format('d M Y, H:i') ?? '-' }}</td>
                            <td class="p-4">
                                @if($r->kondisi_kembali)
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-bold {{ $r->kondisi_kembali == 'Baik' ? 'bg-emerald-100 text-emerald-700 border border-emerald-200' : ($r->kondisi_kembali == 'Rusak Ringan' ? 'bg-amber-100 text-amber-700 border border-amber-200' : 'bg-rose-100 text-rose-700 border border-rose-200') }}" title="{{ $r->catatan_kembali ?? '' }}">
                                        {{ $r->kondisi_kembali }}
                                    </span>
                                @else
                                    <span class="text-slate-400">-</span>
                                @endif
                            </td>
                            <td class="p-4 font-medium text-slate-500 max-w-xs">
                                @if($r->id_kunjungan)
                                    <a href="{{ route('kunjungan.show', $r->id_kunjungan) }}" class="text-[#003399] font-bold hover:underline">{{ $r->kunjungan->nomor ?? '-' }}</a>
                                    <span class="block text-[11px]">{{ $r->kunjungan->customer->nama_perusahaan ?? '' }}</span>
                                @else
                                    <span class="italic">{{ $r->keterangan ?? 'Keperluan lain' }}</span>
                                @endif
                            </td>
                            <td class="p-4">
                                <span class="px-2.5 py-1 rounded-full text-[10px] font-bold {{ $r->status == 'Dipinjam' ? 'bg-amber-100 text-amber-700 border border-amber-200' : 'bg-emerald-100 text-emerald-700 border border-emerald-200' }}">
                                    {{ $r->status }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="p-8 text-center text-slate-400 font-medium italic">Belum ada riwayat peminjaman.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($riwayat->hasPages())
            <div class="p-4 border-t border-slate-200 bg-slate-50">{{ $riwayat->links() }}</div>
        @endif
    </div>
</div>
@endsection
