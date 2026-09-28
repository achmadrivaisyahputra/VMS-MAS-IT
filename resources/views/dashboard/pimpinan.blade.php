@extends('layouts.app')

@section('title', 'Dashboard Pimpinan')
@section('header_title', 'Operasional & Penjadwalan')

@section('content')
<div class="space-y-6">

    <!-- KPI Operasional -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-5">
        <div class="p-5 rounded-2xl bg-white border border-slate-200 shadow-sm">
            <p class="text-xs font-semibold text-slate-500">Kunjungan Hari Ini</p>
            <h3 class="text-2xl font-bold text-[#002266] mt-1">{{ $kunjunganHariIni }}</h3>
            <p class="text-[11px] text-slate-500 mt-2 font-medium">Jadwal tanggal {{ date('d M Y') }}</p>
        </div>
        <div class="p-5 rounded-2xl bg-white border border-slate-200 shadow-sm">
            <p class="text-xs font-semibold text-slate-500">Engineer Tersedia</p>
            <h3 class="text-2xl font-bold text-emerald-600 mt-1">{{ $engineerTersedia }}</h3>
            <p class="text-[11px] text-slate-500 mt-2 font-medium">Siap ditugaskan</p>
        </div>
        <div class="p-5 rounded-2xl bg-white border border-slate-200 shadow-sm">
            <p class="text-xs font-semibold text-slate-500">Tools / Alat Siap Pakai</p>
            <h3 class="text-2xl font-bold text-indigo-600 mt-1">{{ $toolTersedia }}</h3>
            <p class="text-[11px] text-slate-500 mt-2 font-medium">Kondisi baik & tersedia</p>
        </div>
        <div class="p-5 rounded-2xl bg-white border border-slate-200 shadow-sm">
            <p class="text-xs font-semibold text-slate-500">Total Kunjungan</p>
            <h3 class="text-2xl font-bold text-[#002266] mt-1">{{ $totalKunjungan }}</h3>
            <p class="text-[11px] text-slate-500 mt-2 font-medium">Sepanjang masa</p>
        </div>
    </div>

    <!-- Quick Action Card -->
    <div class="p-6 rounded-2xl bg-gradient-to-r from-[#002266] to-[#0044cc] border border-[#001a4d] flex flex-col md:flex-row items-center justify-between gap-4 shadow-lg shadow-blue-900/10">
        <div>
            <h4 class="text-base font-bold text-white">Buat Jadwal Kunjungan Baru</h4>
            <p class="text-xs text-blue-200 mt-1 font-medium">Jadwalkan kunjungan teknis ke lokasi customer dan tugaskan engineer.</p>
        </div>
        <button onclick="window.location.href='{{ route('kunjungan.index') }}'" class="px-5 py-2.5 bg-white hover:bg-slate-50 text-[#002266] text-xs font-bold rounded-xl shadow-md transition">
            + Jadwalkan Kunjungan
        </button>
    </div>

    <!-- Daftar Kunjungan Aktif -->
    <div class="rounded-2xl bg-white border border-slate-200 shadow-sm overflow-hidden">
        <div class="p-6 border-b border-slate-100">
            <h4 class="text-sm font-bold text-[#002266]">Daftar Kunjungan Operasional</h4>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-600">
                <thead class="text-[11px] uppercase bg-slate-50 text-slate-500 border-b border-slate-200">
                    <tr>
                        <th class="p-4 font-bold">Nomor</th>
                        <th class="p-4 font-bold">Customer & Lokasi</th>
                        <th class="p-4 font-bold">Engineer Bertugas</th>
                        <th class="p-4 font-bold">Tools Dibawa</th>
                        <th class="p-4 font-bold">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($kunjunganList as $kunjungan)
                        <tr class="hover:bg-slate-50 transition-colors">
                            <td class="p-4 font-bold text-[#003399]">{{ $kunjungan->nomor }}</td>
                            <td class="p-4">
                                <p class="font-bold text-slate-800">{{ $kunjungan->customer->nama_perusahaan ?? '-' }}</p>
                                <p class="text-[10px] text-slate-500 font-medium">{{ $kunjungan->alamat_sinkron }}</p>
                            </td>
                            <td class="p-4 font-medium">
                                {{ $kunjungan->engineer->user->nama ?? 'Belum Ditugaskan' }}
                            </td>
                            <td class="p-4 font-medium">
                                <span class="text-slate-500">{{ $kunjungan->tools->count() }} Alat Terlampir</span>
                            </td>
                            <td class="p-4">
                                <span class="px-2.5 py-1 rounded-full text-[10px] font-bold {{ $kunjungan->status == 'Selesai' ? 'bg-emerald-100 text-emerald-700' : 'bg-amber-100 text-amber-700' }}">
                                    {{ $kunjungan->status }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="p-6 text-center text-slate-400 italic font-medium">Belum ada aktivitas kunjungan.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <!-- FITUR BARU: Tombol Lihat Semua Kunjungan -->
        <div class="p-4 bg-slate-50 border-t border-slate-100 flex justify-center">
            <a href="{{ route('kunjungan.index') }}" class="text-xs font-bold text-[#003399] hover:text-[#001233] flex items-center gap-1.5 transition">
                Lihat Semua Kunjungan
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            </a>
        </div>
    </div>

</div>
@endsection