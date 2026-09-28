<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('engineers', function (Blueprint $table) {
            $table->string('kode', 20)->nullable()->unique()->after('id_engineer');
        });
    }

    public function down(): void
    {
        Schema::table('engineers', function (Blueprint $table) {
            $table->dropColumn('kode');
        });
    }
};
