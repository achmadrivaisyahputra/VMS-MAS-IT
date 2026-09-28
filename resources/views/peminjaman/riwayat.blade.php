@extends('layouts.app')

@section('title', 'Riwayat Tools')
@section('header_title', 'Riwayat Pemakaian Tools')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h3 class="text-lg font-bold text-[#002266]">{{ $tool->nama_alat }}</h3>
            <p class="text-xs text-slate-500 font-medium mt-0.5">
                <span class="font-mono text-[#003399] font-bold uppercase">{{ $tool->kode }}</span> &middot;
                Stok saat ini: <span class="font-bold text-emerald-600">{{ $tool->stok }}</span> &middot;
                Riwayat pemakaian & pinjaman
            </p>
        </div>
        <a href="{{ url()->previous() }}" class="px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-xs font-bold transition">Kembali</a>
    </div>

    <div class="rounded-2xl bg-white border border-slate-200 overflow-hidden shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-600">
                <thead class="text-[11px] uppercase bg-slate-50 text-slate-500 border-b border-slate-200">
                    <tr>
                        <th class="p-4 font-bold">Engineer</th>
                        <th class="p-4 font-bold text-center">Jumlah</th>
                        <th class="p-4 font-bold">Dipinjam</th>
                        <th class="p-4 font-bold">Dikembalikan</th>
                        <th class="p-4 font-bold">Kondisi Kembali</th>
                        <th class="p-4 font-bold">Keperluan</th>
                        <th class="p-4 font-bold">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($riwayat as $r)
                        <tr class="hover:bg-slate-50 transition-colors">
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
                                    <span class="text-[#003399] font-bold">{{ $r->kunjungan->nomor ?? '-' }}</span>
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
                            <td colspan="7" class="p-8 text-center text-slate-400 font-medium italic">Belum ada riwayat pemakaian untuk tool ini.</td>
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
