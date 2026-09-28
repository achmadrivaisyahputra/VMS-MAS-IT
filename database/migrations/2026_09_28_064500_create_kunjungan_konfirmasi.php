<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kunjungan_konfirmasi', function (Blueprint $table) {
            $table->id('id_konfirmasi');
            $table->unsignedBigInteger('id_kunjungan');
            $table->unsignedBigInteger('id_engineer');
            $table->enum('status', ['menunggu', 'diterima', 'ditolak'])->default('menunggu');
            $table->text('alasan_ditolak')->nullable();
            $table->timestamp('waktu_konfirmasi')->nullable();
            $table->timestamps();

            $table->foreign('id_kunjungan')->references('id_kunjungan')->on('kunjungan')->onDelete('cascade');
            $table->foreign('id_engineer')->references('id_engineer')->on('engineers')->onDelete('cascade');
            $table->unique(['id_kunjungan', 'id_engineer']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kunjungan_konfirmasi');
    }
};
