<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE peminjaman_tools MODIFY COLUMN status ENUM('Dipinjam', 'Dikembalikan', 'Dibatalkan') NOT NULL DEFAULT 'Dipinjam'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE peminjaman_tools MODIFY COLUMN status ENUM('Dipinjam', 'Dikembalikan') NOT NULL DEFAULT 'Dipinjam'");
    }
};
