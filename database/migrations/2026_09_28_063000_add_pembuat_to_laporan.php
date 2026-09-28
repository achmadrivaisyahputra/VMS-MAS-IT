<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('laporan', function (Blueprint $table) {
            $table->unsignedBigInteger('id_engineer_pembuat')->nullable()->after('id_kunjungan');
            $table->foreign('id_engineer_pembuat')->references('id_engineer')->on('engineers')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('laporan', function (Blueprint $table) {
            $table->dropForeign(['id_engineer_pembuat']);
            $table->dropColumn('id_engineer_pembuat');
        });
    }
};
