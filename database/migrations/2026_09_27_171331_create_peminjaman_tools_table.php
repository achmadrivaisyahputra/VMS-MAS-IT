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
        Schema::create('peminjaman_tools', function (Blueprint $table) {
            $table->id('id_peminjaman');
            $table->unsignedBigInteger('id_tool');
            $table->unsignedBigInteger('id_engineer')->nullable();
            $table->unsignedBigInteger('id_kunjungan')->nullable();
            $table->integer('jumlah')->default(1);
            $table->dateTime('tanggal_pinjam');
            $table->dateTime('tanggal_kembali')->nullable();
            $table->enum('status', ['Dipinjam', 'Dikembalikan'])->default('Dipinjam');
            $table->text('keterangan')->nullable();
            $table->timestamps();

            $table->foreign('id_tool')->references('id_tool')->on('tools')->onDelete('cascade');
            $table->foreign('id_engineer')->references('id_engineer')->on('engineers')->onDelete('cascade');
            $table->foreign('id_kunjungan')->references('id_kunjungan')->on('kunjungan')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('peminjaman_tools');
    }
};
