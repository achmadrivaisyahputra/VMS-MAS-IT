<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('aktivitas_pekerjaan', function (Blueprint $table) {
            $table->string('lokasi_checkout')->nullable()->after('lokasi');
        });
    }

    public function down(): void
    {
        Schema::table('aktivitas_pekerjaan', function (Blueprint $table) {
            $table->dropColumn('lokasi_checkout');
        });
    }
};
