@extends('layouts.app')

@section('title', 'Dashboard Kepala Pimpinan')
@section('header_title', 'Monitoring & Analisis Strategis')

@section('content')
<div class="space-y-6">

    <!-- KPI Metric Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-5">
        <div class="p-5 rounded-2xl bg-white border border-slate-200 shadow-sm">
            <p class="text-xs font-semibold text-slate-500">Total Kunjungan</p>
            <h3 class="text-2xl font-bold text-[#002266] mt-1">{{ $totalKunjungan }}</h3>
            <p class="text-[11px] text-[#0044cc] font-medium mt-2">Semua status alur kerja</p>
        </div>
        <div class="p-5 rounded-2xl bg-white border border-slate-200 shadow-sm">
            <p class="text-xs font-semibold text-slate-500">Total Customer</p>
            <h3 class="text-2xl font-bold text-[#002266] mt-1">{{ $totalCustomer }}</h3>
            <p class="text-[11px] text-emerald-600 font-medium mt-2">Klien terdaftar</p>
        </div>
        <div class="p-5 rounded-2xl bg-white border border-slate-200 shadow-sm">
            <p class="text-xs font-semibold text-slate-500">Engineer Aktif</p>
            <h3 class="text-2xl font-bold text-[#002266] mt-1">{{ $totalEngineer }}</h3>
            <p class="text-[11px] text-indigo-600 font-medium mt-2">Tim teknis lapangan</p>
        </div>
        <div class="p-5 rounded-2xl bg-white border border-slate-200 shadow-sm">
            <p class="text-xs font-semibold text-slate-500">Total Tools / Alat</p>
            <h3 class="text-2xl font-bold text-[#002266] mt-1">{{ $totalTool }}</h3>
            <p class="text-[11px] text-amber-600 font-medium mt-2">Aset operasional</p>
        </div>
    </div>

    <!-- Charts & Analytics Section -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        <!-- Status Kunjungan Donut Chart -->
        <div class="p-6 rounded-2xl bg-white border border-slate-200 shadow-sm">
            <h4 class="text-sm font-bold text-[#002266] mb-4">Distribusi Status Kunjungan</h4>
            <div class="h-56 relative flex items-center justify-center">
                <canvas id="statusChart"></canvas>
            </div>
            <div class="grid grid-cols-3 gap-2 mt-4 text-center text-xs">
                <div class="p-2 bg-slate-50 border border-slate-100 rounded-lg">
                    <p class="text-amber-500 font-bold">{{ $kunjunganTerjadwal }}</p>
                    <p class="text-[10px] text-slate-500 font-medium">Terjadwal</p>
                </div>
                <div class="p-2 bg-slate-50 border border-slate-100 rounded-lg">
                    <p class="text-[#0044cc] font-bold">{{ $kunjunganDikerjakan }}</p>
                    <p class="text-[10px] text-slate-500 font-medium">Dikerjakan</p>
                </div>
                <div class="p-2 bg-slate-50 border border-slate-100 rounded-lg">
                    <p class="text-emerald-500 font-bold">{{ $kunjunganSelesai }}</p>
                    <p class="text-[10px] text-slate-500 font-medium">Selesai</p>
                </div>
            </div>
        </div>

        <!-- Analisis Jenis Pekerjaan Terbanyak -->
        <div class="lg:col-span-2 p-6 rounded-2xl bg-white border border-slate-200 shadow-sm">
            <h4 class="text-sm font-bold text-[#002266] mb-4">Jenis Pekerjaan Terbanyak</h4>
            <div class="space-y-4">
                @forelse($pekerjaanTerbanyak as $item)
                    <div>
                        <div class="flex justify-between text-xs font-bold text-slate-700 mb-1">
                            <span>{{ $item->pekerjaan }}</span>
                            <span class="text-[#003399]">{{ $item->total }} Kunjungan</span>
                        </div>
                        <div class="w-full h-2 bg-slate-100 rounded-full overflow-hidden">
                            <div class="h-full bg-gradient-to-r from-[#002266] to-[#0044cc] rounded-full" style="width: {{ ($item->total / max($totalKunjungan, 1)) * 100 }}%"></div>
                        </div>
                    </div>
                @empty
                    <p class="text-xs text-slate-400 italic">Belum ada data kunjungan.</p>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Recent Kunjungan Table -->
    <div class="rounded-2xl bg-white border border-slate-200 shadow-sm overflow-hidden">
        <div class="p-6 border-b border-slate-100">
            <h4 class="text-sm font-bold text-[#002266]">Monitoring Kunjungan Terbaru</h4>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-600">
                <thead class="text-[11px] uppercase bg-slate-50 text-slate-500 border-b border-slate-200">
                    <tr>
                        <th class="p-4 font-bold">Nomor</th>
                        <th class="p-4 font-bold">Customer</th>
                        <th class="p-4 font-bold">Engineer</th>
                        <th class="p-4 font-bold">Pekerjaan</th>
                        <th class="p-4 font-bold">Tanggal & Waktu</th>
                        <th class="p-4 font-bold">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($recentKunjungan as $kunjungan)
                        <tr class="hover:bg-slate-50 transition-colors">
                            <td class="p-4 font-bold text-[#003399]"><a href="{{ route('kunjungan.show', $kunjungan->nomor) }}" class="hover:underline">{{ $kunjungan->nomor }}</a></td>
                            <td class="p-4 font-medium text-slate-800">{{ $kunjungan->customer->nama_perusahaan ?? '-' }}</td>
                            <td class="p-4 font-medium">{{ $kunjungan->engineer->user->nama ?? 'Belum Ditugaskan' }}</td>
                            <td class="p-4 font-medium">{{ $kunjungan->pekerjaan }}</td>
                            <td class="p-4 text-slate-500">{{ $kunjungan->tanggal }} ({{ $kunjungan->waktu }})</td>
                            <td class="p-4">
                                <span class="px-2.5 py-1 rounded-full text-[10px] font-bold {{ $kunjungan->status == 'Selesai' ? 'bg-emerald-100 text-emerald-700' : ($kunjungan->status == 'Dikerjakan' ? 'bg-blue-100 text-blue-700' : 'bg-amber-100 text-amber-700') }}">
                                    {{ $kunjungan->status }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="p-6 text-center text-slate-400 italic">Belum ada kunjungan terbaru.</td>
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

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const ctx = document.getElementById('statusChart').getContext('2d');
        new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: ['Terjadwal', 'Dikerjakan', 'Selesai'],
                datasets: [{
                    data: [{{ $kunjunganTerjadwal }}, {{ $kunjunganDikerjakan }}, {{ $kunjunganSelesai }}],
                    backgroundColor: ['#f59e0b', '#0044cc', '#10b981'],
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false }
                },
                cutout: '70%'
            }
        });
    });
</script>
@endsection