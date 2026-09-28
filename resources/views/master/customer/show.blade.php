@extends('layouts.app')

@section('title', 'Detail Customer - ' . $customer->kode)
@section('header_title', 'Detail Customer')

@section('content')
<div class="space-y-6">
    <a href="{{ route('master.customer.index') }}" class="inline-block px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-xs font-bold transition">&larr; Kembali ke Daftar Customer</a>

    {{-- Info Customer --}}
    <div class="rounded-2xl bg-white border border-slate-200 shadow-sm p-6">
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div>
                <span class="font-mono text-xs text-[#003399] font-bold uppercase bg-blue-50 border border-blue-100 px-2.5 py-1 rounded-lg">{{ $customer->kode }}</span>
                <h3 class="text-xl font-bold text-[#002266] mt-2">{{ $customer->nama_perusahaan }}</h3>
                <p class="text-xs text-slate-500 font-medium mt-1">PIC: <span class="font-bold text-slate-700">{{ $customer->pic }}</span></p>
            </div>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-6 text-xs">
            <div class="bg-slate-50 border border-slate-200 rounded-xl p-4">
                <p class="text-slate-500 font-bold uppercase text-[10px] mb-2">Kontak</p>
                <p class="font-medium text-slate-700">{{ $customer->telepon }}</p>
                <p class="font-medium text-slate-700 mt-1">{{ $customer->email }}</p>
            </div>
            <div class="bg-slate-50 border border-slate-200 rounded-xl p-4">
                <p class="text-slate-500 font-bold uppercase text-[10px] mb-2">Alamat</p>
                <p class="font-medium text-slate-700">{{ $customer->alamat }}</p>
                @if($customer->latitude && $customer->longitude)
                    <p class="text-[11px] font-bold text-emerald-600 mt-2">GPS: {{ $customer->latitude }}, {{ $customer->longitude }}</p>
                @endif
            </div>
        </div>
    </div>

    {{-- Daftar Site / Cabang --}}
    <div class="rounded-2xl bg-white border border-slate-200 overflow-hidden shadow-sm">
        <div class="px-6 py-4 border-b border-slate-200 bg-slate-50">
            <h4 class="text-sm font-bold text-[#002266]">Site / Cabang ({{ $customer->sites->count() }})</h4>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-600">
                <thead class="text-[11px] uppercase bg-slate-50 text-slate-500 border-b border-slate-200">
                    <tr>
                        <th class="p-4 font-bold">Nama Cabang</th>
                        <th class="p-4 font-bold">Alamat Lengkap</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($customer->sites as $s)
                        <tr class="hover:bg-slate-50 transition-colors">
                            <td class="p-4 font-bold text-slate-800">{{ $s->nama_cabang }}</td>
                            <td class="p-4 font-medium">{{ $s->alamat_lengkap }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="2" class="p-6 text-center text-slate-400">Belum ada site/cabang.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Riwayat Kunjungan --}}
    <div class="rounded-2xl bg-white border border-slate-200 overflow-hidden shadow-sm">
        <div class="px-6 py-4 border-b border-slate-200 bg-slate-50">
            <h4 class="text-sm font-bold text-[#002266]">Riwayat Kunjungan</h4>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-600">
                <thead class="text-[11px] uppercase bg-slate-50 text-slate-500 border-b border-slate-200">
                    <tr>
                        <th class="p-4 font-bold">No. Kunjungan</th>
                        <th class="p-4 font-bold">Tanggal</th>
                        <th class="p-4 font-bold">Engineer</th>
                        <th class="p-4 font-bold">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($kunjungans as $k)
                        <tr class="hover:bg-slate-50 transition-colors">
                            <td class="p-4">
                                <a href="{{ route('kunjungan.show', $k->nomor) }}" class="font-mono font-bold text-[#003399] hover:underline">{{ $k->nomor }}</a>
                            </td>
                            <td class="p-4 font-medium">{{ \Carbon\Carbon::parse($k->tanggal)->format('d M Y') }}</td>
                            <td class="p-4 font-bold text-slate-800">{{ $k->engineer->user->nama ?? '-' }}</td>
                            <td class="p-4">
                                <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-blue-100 text-blue-700 border border-blue-200">{{ $k->status }}</span>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="p-6 text-center text-slate-400">Belum ada kunjungan.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($kunjungans->hasPages())
        <div class="px-6 py-4 border-t border-slate-200">{{ $kunjungans->links() }}</div>
        @endif
    </div>
</div>
@endsection
