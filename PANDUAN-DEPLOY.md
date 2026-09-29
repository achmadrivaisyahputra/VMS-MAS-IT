# Panduan Deploy VMS-MAS-IT ke Hosting (mas-it.id)

Checklist agar web berjalan lancar di production tanpa error.

## 1. Syarat hosting

- PHP **8.1 / 8.2 / 8.3** (ekstensi wajib: `pdo_mysql`, `mbstring`, `openssl`,
  `fileinfo`, `gd`, `ctype`, `json`, `bcmath`, `tokenizer`, `xml` — umumnya
  sudah aktif di shared hosting)
- MySQL / MariaDB
- Akses SSH atau terminal (untuk composer & artisan). Jika tidak ada, jalankan
  perintah artisan dari lokal lalu upload hasilnya (lihat catatan).

## 2. Upload file

1. Upload **semua file project** ke hosting (atau `git clone` dari GitHub).
2. Document root / public_html **harus mengarah ke folder `public/`**,
   bukan ke root project. (Di cPanel: atur via "Domains" > document root.)
3. **Jangan upload** `vendor/` bila akan menjalankan `composer install` di
   hosting. Jika hosting tanpa SSH/composer, upload `vendor/` dari lokal.

## 3. Konfigurasi .env

1. Copy `.env.production.example` menjadi `.env`.
2. Isi `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD` sesuai database di hosting.
3. Set `APP_URL=https://mas-it.id` (sesuai domain).
4. Jalankan: `php artisan key:generate` (mengisi `APP_KEY`).
5. Pastikan `APP_DEBUG=false` dan `APP_ENV=production`.

## 4. Database & permission (jalankan berurutan)

```bash
composer install --no-dev --optimize-autoloader
php artisan migrate --force
php artisan db:seed --force
php artisan db:seed --class=DummyDataSeeder --force   # data demo (opsional)
php artisan config:cache
php artisan route:cache
php artisan view:cache
chmod -R 775 storage bootstrap/cache
chmod -R 775 public/uploads public/pdf_preview
```

Catatan:
- Folder `public/uploads/*` dan `public/pdf_preview/` **dibuat otomatis**
  oleh aplikasi bila belum ada, tetapi pastikan writable oleh web server.
- Fitur **preview PDF**: di hosting yang memiliki `pdftoppm` (poppler-utils),
  preview tampil sebagai gambar. Di shared hosting yang tidak memilikinya,
  aplikasi **otomatis fallback** menampilkan PDF langsung di browser —
  fitur tetap jalan, tidak error.

## 5. Verifikasi

1. Buka `https://mas-it.id/login` — harus tampil halaman login (HTTP 200).
2. Login dengan akun demo (`simon` / `pimpinan` / `andi.engineer`,
   password: `masitno1indonesia` bila seeder demo dijalankan).
3. Coba: buat kunjungan → check-in → buat laporan → cetak PDF (preview).
4. Coba upload foto dokumentasi & bukti nota.

## 6. Jika ada error di production

- Cek `storage/logs/laravel.log` (jangan panik: dengan `APP_DEBUG=false`,
  pengunjung hanya melihat halaman error umum, detail ada di log).
- Error umum: permission `storage/` (jalankan chmod di atas), kredensial DB
  salah di `.env`, atau `APP_KEY` kosong.
- Setelah mengubah `.env`, jalankan `php artisan config:clear`
  (atau `config:cache` ulang).
