<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Roles
        DB::table('roles')->insert([
            ['id_role' => 1, 'nama_role' => 'Kepala Pimpinan', 'keterangan' => 'Supervisi & Analisis Strategis'],
            ['id_role' => 2, 'nama_role' => 'Pimpinan', 'keterangan' => 'Operasional & Penjadwalan'],
            ['id_role' => 3, 'nama_role' => 'Engineer', 'keterangan' => 'Pelaksana Lapangan'],
        ]);

        // 2. Akun Pengguna
        $adminId = DB::table('pengguna')->insertGetId([
            'nama' => 'Simon Lamakadu',
            'username' => 'simon',
            'email' => 'simon@mas-it.id',
            'password' => Hash::make('masitno1indonesia'),
            'kontak' => '0818788831',
            'id_role' => 1,
            'status_akun' => 'Aktif',
            'created_at' => now(),
        ]);

        $pimpinanId = DB::table('pengguna')->insertGetId([
            'nama' => 'Pimpinan',
            'username' => 'pimpinan',
            'email' => 'pimpinan@mas-it.id',
            'password' => Hash::make('masitno1indonesia'),
            'kontak' => '081234567891',
            'id_role' => 2,
            'status_akun' => 'Aktif',
            'dibuat_oleh' => $adminId,
            'created_at' => now(),
        ]);

        // Format penomoran (prefix) — bisa diubah lewat menu Master Data
        $this->call(FormatNomorSeeder::class);

    }
}