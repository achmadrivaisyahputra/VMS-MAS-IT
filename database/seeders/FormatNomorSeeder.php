<?php

namespace Database\Seeders;

use App\Models\FormatNomor;
use Illuminate\Database\Seeder;

class FormatNomorSeeder extends Seeder
{
    public function run(): void
    {
        $defaults = [
            [
                'kode' => 'kunjungan',
                'jenis' => 'ID Kunjungan',
                'deskripsi' => 'Nomor unik berurutan untuk setiap kunjungan teknisi. Naik +1 setiap ada kunjungan baru.',
                'prefix' => 'vmsmit',
                'tahun' => 2026,
                'digit' => 3,
                'nomor_terakhir' => 0,
            ],
            [
                'kode' => 'customer',
                'jenis' => 'Kode Customer',
                'deskripsi' => 'Kode unik berurutan untuk setiap customer/perusahaan yang didaftarkan.',
                'prefix' => 'cst',
                'tahun' => 2026,
                'digit' => 3,
                'nomor_terakhir' => 0,
            ],
            [
                'kode' => 'tool',
                'jenis' => 'Kode Tool',
                'deskripsi' => 'Kode unik berurutan untuk setiap tool/alat kerja yang didaftarkan.',
                'prefix' => 'tls',
                'tahun' => 2026,
                'digit' => 3,
                'nomor_terakhir' => 0,
            ],
            [
                'kode' => 'engineer',
                'jenis' => 'Kode Engineer',
                'deskripsi' => 'Kode unik berurutan untuk setiap engineer/teknisi yang didaftarkan.',
                'prefix' => 'eng',
                'tahun' => 2026,
                'digit' => 3,
                'nomor_terakhir' => 0,
            ],
        ];

        foreach ($defaults as $row) {
            // Jangan timpa nomor_terakhir yang sudah berjalan — hanya isi default saat pertama kali dibuat
            $existing = FormatNomor::where('kode', $row['kode'])->first();
            if ($existing) {
                $row['nomor_terakhir'] = $existing->nomor_terakhir;
            }
            FormatNomor::updateOrCreate(['kode' => $row['kode']], $row);
        }
    }
}
