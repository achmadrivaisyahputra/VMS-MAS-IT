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
        Schema::table('kunjungan', function (Blueprint $table) {
            // Draft TTD sementara: tersimpan otomatis saat pad dikunci,
            // agar tidak hilang jika halaman di-refresh sebelum submit final.
            $table->longText('draft_ttd_customer')->nullable()->after('patokan');
            $table->longText('draft_ttd_engineer')->nullable()->after('draft_ttd_customer');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('kunjungan', function (Blueprint $table) {
            $table->dropColumn(['draft_ttd_customer', 'draft_ttd_engineer']);
        });
    }
};
