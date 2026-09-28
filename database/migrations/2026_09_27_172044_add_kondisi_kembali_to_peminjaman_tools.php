<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('peminjaman_tools', function (Blueprint $table) {
            $table->string('kondisi_kembali', 50)->nullable()->after('tanggal_kembali');
            $table->text('catatan_kembali')->nullable()->after('kondisi_kembali');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('peminjaman_tools', function (Blueprint $table) {
            $table->dropColumn(['kondisi_kembali', 'catatan_kembali']);
        });
    }
};
