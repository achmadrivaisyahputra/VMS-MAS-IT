<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Perbaikan: kode memakai status 'Dikonfirmasi' dan 'Reschedule'
     * tapi kolom enum hanya mengizinkan Terjadwal/Dikerjakan/Selesai,
     * sehingga tombol "Terima & Konfirmasi Jadwal" error 1265.
     */
    public function up(): void
    {
        DB::statement("ALTER TABLE `kunjungan` MODIFY `status` ENUM('Terjadwal','Dikonfirmasi','Dikerjakan','Selesai','Reschedule') NOT NULL DEFAULT 'Terjadwal'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE `kunjungan` MODIFY `status` ENUM('Terjadwal','Dikerjakan','Selesai') NOT NULL DEFAULT 'Terjadwal'");
    }
};
