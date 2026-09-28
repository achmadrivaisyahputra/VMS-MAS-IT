<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('aktivitas_pekerjaan', function (Blueprint $table) {
            $table->unsignedBigInteger('id_engineer')->nullable()->after('id_kunjungan');
            $table->foreign('id_engineer')->references('id_engineer')->on('engineers')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('aktivitas_pekerjaan', function (Blueprint $table) {
            $table->dropForeign(['id_engineer']);
            $table->dropColumn('id_engineer');
        });
    }
};
