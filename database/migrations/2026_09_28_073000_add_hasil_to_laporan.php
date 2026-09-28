<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('laporan', function (Blueprint $table) {
            $table->text('hasil_pekerjaan')->nullable()->after('status_laporan');
            $table->text('catatan_tambahan')->nullable()->after('hasil_pekerjaan');
        });
    }

    public function down(): void
    {
        Schema::table('laporan', function (Blueprint $table) {
            $table->dropColumn(['hasil_pekerjaan', 'catatan_tambahan']);
        });
    }
};
