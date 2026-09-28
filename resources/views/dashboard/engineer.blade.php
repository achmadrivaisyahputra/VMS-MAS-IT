@extends('layouts.app')

@section('title', 'Dashboard Engineer')
@section('header_title', 'Area Tugas Lapangan')

@section('content')
<div class="space-y-6">

    <!-- D-DAY ALERT BANNER -->
    @if($alertHariIni->count() > 0)
        <div class="p-4 rounded-2xl bg-rose-50 border border-rose-200 shadow-sm flex flex-col sm:flex-row items-center justify-between gap-4 animate-pulse-slow">
            <div class="flex items-center gap-3 text-rose-700">
                <div class="p-2 bg-rose-100 rounded-full shrink-0">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                </div>
                <div>
                    <h3 class="font-bold text-sm md:text-base uppercase tracking-wider">PENGINGAT HARI H!</h3>
                    <p class="text-xs md:text-sm text-rose-600">Anda memiliki <strong>{{ $alertHariIni->count() }} jadwal kunjungan</strong> yang harus dilaksanakan hari ini.</p>
                </div>
            </div>
        </div>
        <style>
            .animate-pulse-slow { animation: pulse-slow 3s cubic-bezier(0.4, 0, 0.6, 1) infinite; }
            @keyframes pulse-slow { 0%, 100% { opacity: 1; } 50% { opacity: 0.8; } }
        </style>
    @endif

    <!-- Status Engineer Profile -->
    <div class="p-5 md:p-6 rounded-2xl bg-white border border-slate-200 shadow-sm flex items-center justify-between">
        <div>
            <p class="text-xs text-slate-500 font-medium">Selamat Bekerja,</p>
            <h3 class="text-lg md:text-xl font-bold text-[#002266] mt-0.5">{{ Auth::user()->nama }}</h3>
            <p class="text-xs text-blue-600 mt-1">Status Ketersediaan: <span class="font-semibold">{{ $engineer->status_ketersediaan ?? 'Tersedia' }}</span></p>
        </div>
        <div class="w-10 h-10 md:w-12 md:h-12 rounded-xl bg-blue-50 border border-blue-100 flex items-center justify-center text-[#003399] shrink-0">
            <svg class="w-5 h-5 md:w-6 md:h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
        </div>
    </div>

    <!-- Active Task Card -->
    <div class="p-5 md:p-6 rounded-2xl bg-white border border-slate-200 shadow-sm">
        <h4 class="text-sm font-bold text-[#002266] mb-4 flex items-center gap-2">
            <span class="w-2.5 h-2.5 rounded-full bg-amber-400 animate-ping"></span>
            Tugas Kunjungan Aktif Saat Ini
        </h4>

        @if($kunjunganAktif)
            <div class="p-4 rounded-xl bg-blue-50/50 border border-blue-100 space-y-4">
                <div class="flex flex-col sm:flex-row justify-between items-start gap-2">
                    <div>
                        <span class="px-2 py-0.5 rounded bg-blue-100 text-[#003399] text-[10px] font-bold uppercase">{{ $kunjunganAktif->nomor }}</span>
                        <h5 class="text-base font-bold text-slate-800 mt-2">{{ $kunjunganAktif->pekerjaan }}</h5>
                        <p class="text-xs text-slate-600 font-semibold">{{ $kunjunganAktif->customer->nama_perusahaan ?? '-' }}</p>
                        <p class="text-xs text-slate-500 mt-0.5">📍 {{ $kunjunganAktif->alamat_sinkron }}</p>
                        @if($kunjunganAktif->patokan)
                            <p class="text-[11px] text-amber-600 font-semibold mt-0.5">📎 {{ $kunjunganAktif->patokan }}</p>
                        @endif
                        
                        <p class="text-[10px] mt-2 inline-block px-2 py-1 rounded-md bg-white border border-slate-200 text-slate-600 font-medium">
                            Peran Anda: <strong class="text-[#003399]">{{ $kunjunganAktif->id_engineer == $engineer->id_engineer ? 'Lead Engineer' : 'Tim Support' }}</strong>
                        </p>
                    </div>
                    <span class="px-3 py-1 rounded-full text-xs font-bold bg-amber-100 text-amber-600 border border-amber-200">
                        {{ $kunjunganAktif->status }}
                    </span>
                </div>

                <div class="pt-3 border-t border-blue-100 flex flex-wrap gap-2">
                    <a href="{{ route('kunjungan.show', $kunjunganAktif->id_kunjungan) }}" 
                       class="w-full sm:w-auto px-5 py-2.5 bg-gradient-to-r from-[#002266] to-[#0044cc] hover:from-[#001a4d] hover:to-[#003399] text-white rounded-xl font-semibold text-xs text-center shadow-lg shadow-blue-900/20 flex items-center justify-center gap-2 transition-all">
                        <span>Buka Lembar Kerja Kunjungan</span>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                    </a>
                </div>
            </div>
        @else
            <div class="p-8 text-center bg-slate-50 rounded-xl border border-slate-200">
                <p class="text-xs text-slate-500 font-medium">Tidak ada penugasan aktif saat ini. Anda berada dalam status standby.</p>
            </div>
        @endif
    </div>

    <!-- Riwayat Kunjungan Selesai -->
    <div class="rounded-2xl bg-white border border-slate-200 shadow-sm overflow-hidden">
        <div class="p-5 md:p-6 border-b border-slate-100">
            <h4 class="text-sm font-bold text-[#002266]">Riwayat Kunjungan Selesai Terakhir</h4>
        </div>
        <div class="p-5 md:p-6 space-y-3">
            @forelse($riwayatKunjungan as $history)
                <div class="p-3 bg-slate-50 border border-slate-100 rounded-xl flex items-center justify-between text-xs">
                    <div>
                        <p class="font-bold text-slate-700">{{ $history->pekerjaan }}</p>
                        <p class="text-[11px] text-slate-500 font-medium">{{ $history->customer->nama_perusahaan ?? '-' }} • {{ $history->tanggal }}</p>
                    </div>
                    <a href="{{ route('kunjungan.show', $history->id_kunjungan) }}" class="text-emerald-600 font-bold text-[11px] hover:underline">
                        Lihat Detail ✔
                    </a>
                </div>
            @empty
                <p class="text-xs text-slate-400 italic">Belum ada riwayat kunjungan selesai.</p>
            @endforelse
        </div>
        
        <!-- FITUR BARU: Tombol Lihat Semua Kunjungan -->
        <div class="p-4 bg-slate-50 border-t border-slate-100 flex justify-center">
            <a href="{{ route('kunjungan.index') }}" class="text-xs font-bold text-[#003399] hover:text-[#001233] flex items-center gap-1.5 transition">
                Lihat Semua Riwayat Kunjungan
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            </a>
        </div>
    </div>

</div>
@endsection