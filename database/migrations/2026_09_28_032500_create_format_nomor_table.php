<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('format_nomor', function (Blueprint $table) {
            $table->id();
            $table->string('kode', 50)->unique()->comment('Kunci unik: kunjungan, customer, dll');
            $table->string('jenis', 100)->comment('Label: ID Kunjungan');
            $table->text('deskripsi')->nullable()->comment('Penjelasan ID ini untuk apa');
            $table->string('prefix', 50)->comment('Awalan: vmsmit');
            $table->year('tahun')->comment('Tahun: 2026');
            $table->unsignedTinyInteger('digit')->default(3)->comment('Jumlah digit counter: 3 -> 001');
            $table->unsignedBigInteger('nomor_terakhir')->default(0)->comment('Counter terakhir yang dipakai');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('format_nomor');
    }
};
