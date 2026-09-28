<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DummyDataSeeder extends Seeder
{
    public function run(): void
    {
        // ================= DATA CUSTOMER =================
        $customers = [
            [
                'nama_perusahaan' => 'PT. Maju Jaya Abadi',
                'alamat' => 'Jl. Sudirman Kav. 52-53, Jakarta Selatan',
                'pic' => 'Budi Santoso', 'telepon' => '081234567001',
                'email' => 'budi@majujaya.co.id',
                'latitude' => -6.22501400, 'longitude' => 106.80237100,
            ],
            [
                'nama_perusahaan' => 'PT. Sinar Terang Perkasa',
                'alamat' => 'Jl. Asia Afrika No. 88, Bandung',
                'pic' => 'Dewi Lestari', 'telepon' => '081234567002',
                'email' => 'dewi@sinarterang.co.id',
                'latitude' => -6.92184500, 'longitude' => 107.60708300,
            ],
            [
                'nama_perusahaan' => 'PT. Berkah Alam Nusantara',
                'alamat' => 'Jl. Pemuda No. 60-70, Surabaya',
                'pic' => 'Agus Wijaya', 'telepon' => '081234567003',
                'email' => 'agus@berkahalam.co.id',
                'latitude' => -7.25956200, 'longitude' => 112.74619300,
            ],
            [
                'nama_perusahaan' => 'PT. Cahaya Digital Indonesia',
                'alamat' => 'Jl. MH Thamrin No. 59, Jakarta Pusat',
                'pic' => 'Rina Marlina', 'telepon' => '081234567004',
                'email' => 'rina@cahayadigital.co.id',
                'latitude' => -6.19312500, 'longitude' => 106.82180200,
            ],
            [
                'nama_perusahaan' => 'PT. Tunas Bangsa Sejahtera',
                'alamat' => 'Jl. Gatot Subroto No. 200, Medan',
                'pic' => 'Hendra Gunawan', 'telepon' => '081234567005',
                'email' => 'hendra@tunasbangsa.co.id',
                'latitude' => 3.58333300, 'longitude' => 98.66666700,
            ],
        ];

        $customerIds = [];
        $fmtCustomer = DB::table('format_nomor')->where('jenis', 'Kode Customer')->first();
        foreach ($customers as $c) {
            $exists = DB::table('customers')->where('nama_perusahaan', $c['nama_perusahaan'])->first();
            if ($exists) {
                $customerIds[$c['nama_perusahaan']] = $exists->id_customer;
                // Backfill kode kalau belum ada
                if (empty($exists->kode) && $fmtCustomer) {
                    $newCounter = $fmtCustomer->nomor_terakhir + 1;
                    $tahunPart = $fmtCustomer->tahun ? substr($fmtCustomer->tahun, -2) : '';
                    $newKode = $fmtCustomer->prefix . $tahunPart . str_pad($newCounter, $fmtCustomer->digit, '0', STR_PAD_LEFT);
                    DB::table('customers')->where('id_customer', $exists->id_customer)->update(['kode' => $newKode]);
                    DB::table('format_nomor')->where('jenis', 'Kode Customer')->update(['nomor_terakhir' => $newCounter]);
                    $fmtCustomer->nomor_terakhir = $newCounter;
                }
                continue;
            }
            // Generate kode customer otomatis
            if ($fmtCustomer) {
                $newCounter = $fmtCustomer->nomor_terakhir + 1;
                $tahunPart = $fmtCustomer->tahun ? substr($fmtCustomer->tahun, -2) : '';
                $c['kode'] = $fmtCustomer->prefix . $tahunPart . str_pad($newCounter, $fmtCustomer->digit, '0', STR_PAD_LEFT);
                DB::table('format_nomor')->where('jenis', 'Kode Customer')->update(['nomor_terakhir' => $newCounter]);
                $fmtCustomer->nomor_terakhir = $newCounter;
            }
            $c['created_at'] = now();
            $c['updated_at'] = now();
            $customerIds[$c['nama_perusahaan']] = DB::table('customers')->insertGetId($c);
        }

        // ================= DATA SITE (terkoneksi ke customer) =================
        $sites = [
            'PT. Maju Jaya Abadi' => [
                ['Kantor Pusat Jakarta', 'Jl. Sudirman Kav. 52-53, Jakarta Selatan', '-6.225014', '106.802371'],
                ['Cabang Depok', 'Jl. Margonda Raya No. 100, Depok', '-6.372625', '106.828917'],
                ['Cabang Bekasi', 'Jl. Ahmad Yani No. 5, Bekasi', '-6.238824', '106.975637'],
            ],
            'PT. Sinar Terang Perkasa' => [
                ['Kantor Pusat Bandung', 'Jl. Asia Afrika No. 88, Bandung', '-6.921845', '107.607083'],
                ['Cabang Cimahi', 'Jl. Raya Cimahi No. 45, Cimahi', '-6.884088', '107.541048'],
            ],
            'PT. Berkah Alam Nusantara' => [
                ['Kantor Pusat Surabaya', 'Jl. Pemuda No. 60-70, Surabaya', '-7.259562', '112.746193'],
                ['Gudang Sidoarjo', 'Jl. Raya Waru No. 12, Sidoarjo', '-7.353871', '112.722090'],
                ['Cabang Gresik', 'Jl. Veteran No. 77, Gresik', '-7.159975', '112.650668'],
            ],
            'PT. Cahaya Digital Indonesia' => [
                ['Head Office Thamrin', 'Jl. MH Thamrin No. 59, Jakarta Pusat', '-6.193125', '106.821802'],
                ['Data Center Cibitung', 'Kawasan Industri MM2100, Cibitung', '-6.322012', '107.096914'],
            ],
            'PT. Tunas Bangsa Sejahtera' => [
                ['Kantor Pusat Medan', 'Jl. Gatot Subroto No. 200, Medan', '3.583333', '98.666667'],
            ],
        ];

        foreach ($sites as $namaPerusahaan => $daftarSite) {
            $idCustomer = $customerIds[$namaPerusahaan] ?? null;
            if (!$idCustomer) continue;
            foreach ($daftarSite as [$namaCabang, $alamat, $lat, $lng]) {
                $exists = DB::table('customer_sites')
                    ->where('id_customer', $idCustomer)
                    ->where('nama_cabang', $namaCabang)->first();
                if ($exists) continue;
                DB::table('customer_sites')->insert([
                    'id_customer' => $idCustomer,
                    'nama_cabang' => $namaCabang,
                    'alamat_lengkap' => $alamat,
                    'latitude' => $lat,
                    'longitude' => $lng,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        // ================= DATA ENGINEER (butuh akun pengguna role Engineer) =================
        $engineers = [
            ['nama' => 'Andi Pratama', 'username' => 'andi.engineer', 'email' => 'andi@mas-it.id', 'kontak' => '081234567101'],
            ['nama' => 'Siti Rahayu', 'username' => 'siti.engineer', 'email' => 'siti@mas-it.id', 'kontak' => '081234567102'],
            ['nama' => 'Joko Susilo', 'username' => 'joko.engineer', 'email' => 'joko@mas-it.id', 'kontak' => '081234567103'],
            ['nama' => 'Putri Ananda', 'username' => 'putri.engineer', 'email' => 'putri@mas-it.id', 'kontak' => '081234567104'],
        ];

        foreach ($engineers as $e) {
            $pengguna = DB::table('pengguna')->where('username', $e['username'])->first();
            if (!$pengguna) {
                $idPengguna = DB::table('pengguna')->insertGetId([
                    'nama' => $e['nama'],
                    'username' => $e['username'],
                    'email' => $e['email'],
                    'password' => Hash::make('masitno1indonesia'),
                    'kontak' => $e['kontak'],
                    'id_role' => 3, // Engineer
                    'status_akun' => 'Aktif',
                    'created_at' => now(),
                ]);
            } else {
                $idPengguna = $pengguna->id_pengguna;
            }

            $exists = DB::table('engineers')->where('id_pengguna', $idPengguna)->first();
            if (!$exists) {
                DB::table('engineers')->insert([
                    'id_pengguna' => $idPengguna,
                    'kontak' => $e['kontak'],
                    'status_ketersediaan' => 'Tersedia',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        // ================= DATA TOOLS =================
        $tools = [
            ['Laptop Service Lenovo ThinkPad', 'TL-LTP-001', 'Komputer', 'Core i7, RAM 16GB, SSD 512GB', 'Baik', 3, 'Tersedia', 'Laptop utama teknisi lapangan'],
            ['Laptop Service HP ProBook', 'TL-LTP-002', 'Komputer', 'Core i5, RAM 8GB, SSD 256GB', 'Baik', 2, 'Tersedia', 'Laptop cadangan'],
            ['LAN Cable Tester', 'TL-NET-001', 'Jaringan', 'Tester kabel UTP RJ45/RJ11', 'Baik', 5, 'Tersedia', null],
            ['Multimeter Digital', 'TL-ELC-001', 'Elektronik', 'Sanwa CD800a, True RMS', 'Baik', 4, 'Tersedia', null],
            ['Obeng Set Presisi 32pcs', 'TL-MKN-001', 'Mekanik', 'Obeng magnetik presisi untuk elektronik', 'Baik', 6, 'Tersedia', null],
            ['Tang Potong & Tang Lancip', 'TL-MKN-002', 'Mekanik', 'Set tang Tekiro', 'Rusak Ringan', 0, 'Tidak Tersedia', 'Gagang tang longgar, perlu servis'],
            ['USB to Serial Adapter', 'TL-NET-002', 'Jaringan', 'Adapter console untuk konfigurasi switch/router', 'Baik', 5, 'Tersedia', null],
            ['Harddisk Eksternal 1TB', 'TL-STG-001', 'Penyimpanan', 'Seagate Backup Plus 1TB', 'Baik', 2, 'Tersedia', 'Untuk backup data customer'],
            ['Kabel UTP Cat6 50m', 'TL-NET-003', 'Jaringan', 'Roll kabel UTP Cat6 Belden', 'Baik', 8, 'Tersedia', null],
            ['Thermal Paste & Cleaning Kit', 'TL-MKN-003', 'Mekanik', 'Kit pembersih dan pasta prosesor', 'Baik', 10, 'Tersedia', null],
        ];

        foreach ($tools as [$nama, $kode, $kategori, $spesifikasi, $kondisi, $stok, $status, $keterangan]) {
            $exists = DB::table('tools')->where('kode', $kode)->first();
            if ($exists) {
                DB::table('tools')->where('kode', $kode)->update(['stok' => $stok]);
                continue;
            }
            DB::table('tools')->insert([
                'nama_alat' => $nama,
                'kode' => $kode,
                'kategori' => $kategori,
                'spesifikasi' => $spesifikasi,
                'kondisi' => $kondisi,
                'stok' => $stok,
                'status_ketersediaan' => $status,
                'keterangan' => $keterangan,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
