#!/bin/bash
# Setup otomatis aplikasi VMS-MAS-IT di GitHub Codespace.
# Dijalankan sekali saat codespace pertama kali dibuat (onCreateCommand).
set -euo pipefail
cd "$(dirname "$0")/.."

MARKER=".devcontainer/.setup-done"
if [ -f "$MARKER" ]; then
  echo "[vms-setup] sudah pernah setup, lewati."
  exit 0
fi

echo "[vms-setup] apt update..."
sudo apt-get update -qq

echo "[vms-setup] install PHP 8.3 + ekstensi..."
if ! apt-cache show php8.3-cli >/dev/null 2>&1; then
  echo "[vms-setup] php8.3 tidak ada di repo bawaan, tambah PPA ondrej/php..."
  sudo apt-get install -y -qq software-properties-common
  sudo add-apt-repository -y ppa:ondrej/php >/dev/null
  sudo apt-get update -qq
fi
sudo DEBIAN_FRONTEND=noninteractive apt-get install -y -qq \
  php8.3-cli php8.3-mysql php8.3-mbstring php8.3-xml php8.3-bcmath \
  php8.3-curl php8.3-zip php8.3-gd composer mysql-server

echo "[vms-setup] start MySQL..."
sudo service mysql start || sudo service mysql restart

echo "[vms-setup] buat database + user..."
sudo mysql -e "CREATE DATABASE IF NOT EXISTS laravel CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
sudo mysql -e "CREATE USER IF NOT EXISTS 'laravel'@'127.0.0.1' IDENTIFIED BY 'secret';"
sudo mysql -e "CREATE USER IF NOT EXISTS 'laravel'@'localhost' IDENTIFIED BY 'secret';"
sudo mysql -e "GRANT ALL PRIVILEGES ON laravel.* TO 'laravel'@'127.0.0.1';"
sudo mysql -e "GRANT ALL PRIVILEGES ON laravel.* TO 'laravel'@'localhost';"
sudo mysql -e "FLUSH PRIVILEGES;"

echo "[vms-setup] composer install..."
composer install --no-interaction --prefer-dist --no-progress

echo "[vms-setup] .env..."
[ -f .env ] || cp .env.example .env
php artisan key:generate --force
sed -i 's/^DB_DATABASE=.*/DB_DATABASE=laravel/' .env
sed -i 's/^DB_USERNAME=.*/DB_USERNAME=laravel/' .env
sed -i 's/^DB_PASSWORD=.*/DB_PASSWORD=secret/' .env

echo "[vms-setup] migrate + seed..."
php artisan migrate --force
php artisan db:seed --force
php artisan db:seed --class=DummyDataSeeder --force
php artisan tinker --execute="use Illuminate\Support\Facades\DB; use Illuminate\Support\Facades\Hash; DB::table('pengguna')->updateOrInsert(['username'=>'engineer'], ['nama'=>'Engineer Demo','email'=>'engineer@mas-it.id','password'=>Hash::make('masitno1indonesia'),'kontak'=>'081234567890','id_role'=>3,'status_akun'=>'Aktif','created_at'=>now(),'updated_at'=>now()]); echo 'engineer-ok';"
php artisan storage:link || true

touch "$MARKER"
echo "[vms-setup] SELESAI. Aplikasi siap dijalankan."
