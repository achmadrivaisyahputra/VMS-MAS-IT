<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Engineer;
use App\Models\Kunjungan;
use App\Models\Tool;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    // Logika untuk nampilin dashboard Kepala (Fokus ke data analitik/statistik keseluruhan)
    public function kepalaDashboard()
    {
        // Ngitung total-totalan semua data master buat ditaruh di kartu paling atas
        $totalKunjungan = Kunjungan::count();
        $totalCustomer = Customer::count();
        $totalEngineer = Engineer::count();
        $totalTool = Tool::count();

        // Ngitung rincian kunjungan berdasarkan statusnya buat dibikin grafik Donut
        $kunjunganTerjadwal = Kunjungan::where('status', 'Terjadwal')->count();
        $kunjunganDikerjakan = Kunjungan::where('status', 'Dikerjakan')->count();
        $kunjunganSelesai = Kunjungan::where('status', 'Selesai')->count();

        // Ambil 5 data kunjungan paling baru buat tabel monitoring singkat
        $recentKunjungan = Kunjungan::with(['customer', 'engineer.user'])
            ->latest()
            ->take(5)
            ->get();

        // Ngambil top 5 jenis pekerjaan yang paling sering dilakuin, digrup pake query raw SQL
        $pekerjaanTerbanyak = Kunjungan::select('pekerjaan', DB::raw('count(*) as total'))
            ->groupBy('pekerjaan')
            ->orderByDesc('total')
            ->take(5)
            ->get();

        // Oper semua variabelnya ke view dashboard kepala
        return view('dashboard.kepala', compact(
            'totalKunjungan', 'totalCustomer', 'totalEngineer', 'totalTool',
            'kunjunganTerjadwal', 'kunjunganDikerjakan', 'kunjunganSelesai',
            'recentKunjungan', 'pekerjaanTerbanyak'
        ));
    }

    // Logika untuk nampilin dashboard Pimpinan (Fokus ke operasional & jadwal harian)
    public function pimpinanDashboard()
    {
        $totalKunjungan = Kunjungan::count();
        
        // Cek berapa banyak alat dan orang yang lagi nganggur (siap ditugasin)
        $toolTersedia = Tool::where('status_ketersediaan', 'Tersedia')->count();
        $engineerTersedia = Engineer::where('status_ketersediaan', 'Tersedia')->count();
        
        // Filter spesifik buat nyari jadwal kunjungan yang tanggalnya hari ini aja
        $kunjunganHariIni = Kunjungan::whereDate('tanggal', now()->toDateString())->count();

        // Tarik 6 kunjungan terakhir beserta relasi datanya buat ditampilin di tabel operasional
        $kunjunganList = Kunjungan::with(['customer', 'site', 'engineer.user', 'tools'])
            ->latest()
            ->take(6)
            ->get();

        return view('dashboard.pimpinan', compact(
            'totalKunjungan', 'toolTersedia', 'engineerTersedia', 'kunjunganHariIni', 'kunjunganList'
        ));
    }

    // Logika untuk nampilin dashboard Engineer Lapangan (Fokus ke task mereka sendiri)
    public function engineerDashboard()
    {
        // Cari tau siapa engineer yang lagi login sekarang
        $user = Auth::user();
        $engineer = Engineer::where('id_pengguna', $user->id_pengguna)->first();

        // Siapin keranjang kosong buat jaga-jaga kalau datanya gak ada
        $kunjunganAktif = null;
        $riwayatKunjungan = collect();
        $alertHariIni = collect(); // Variabel penampung tugas hari H

        if ($engineer) {
            $engineerId = $engineer->id_engineer;

            // 1. Tugas Aktif: SEMUA kunjungan yang belum selesai (Terjadwal/Dikonfirmasi/Dikerjakan), sebagai Lead atau Support
            $tugasAktif = Kunjungan::with(['customer', 'site', 'tools'])
                ->whereIn('status', ['Terjadwal', 'Dikonfirmasi', 'Dikerjakan'])
                ->where(function($q) use ($engineerId) {
                    $q->where('id_engineer', $engineerId)
                      ->orWhereHas('supportEngineers', function($sq) use ($engineerId) {
                          $sq->where('engineers.id_engineer', $engineerId);
                      });
                })
                ->orderBy('tanggal', 'asc')
                ->get();
            // Untuk kompatibilitas: tugas terbaru sebagai highlight
            $kunjunganAktif = $tugasAktif->first();

            // 2. Riwayat Selesai: Ngambil 5 histori pekerjaan dia yang udah sukses kelar
            $riwayatKunjungan = Kunjungan::with(['customer', 'site'])
                ->where('status', 'Selesai')
                ->where(function($q) use ($engineerId) {
                    $q->where('id_engineer', $engineerId)
                      ->orWhereHas('supportEngineers', function($sq) use ($engineerId) {
                          $sq->where('engineers.id_engineer', $engineerId);
                      });
                })
                ->latest()
                ->take(5)
                ->get();

            // 3. LOGIC D-DAY ALERT: Nyari spesifik jadwal dia (sebagai Lead/Support) yang harus dieksekusi hari ini
            $alertHariIni = Kunjungan::with(['customer', 'site'])
                ->whereDate('tanggal', now()->toDateString())
                ->where('status', 'Terjadwal')
                ->where(function($q) use ($engineerId) {
                    $q->where('id_engineer', $engineerId)
                      ->orWhereHas('supportEngineers', function($sq) use ($engineerId) {
                          $sq->where('engineers.id_engineer', $engineerId);
                      });
                })
                ->get();
        }

        return view('dashboard.engineer', compact('engineer', 'kunjunganAktif', 'tugasAktif', 'riwayatKunjungan', 'alertHariIni'));
    }
}