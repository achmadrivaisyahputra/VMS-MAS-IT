#!/bin/bash
# Dijalankan setiap codespace start (termasuk bangun dari idle-stop).
# Memastikan MySQL jalan dan aplikasi serve di port 8000.
cd "$(dirname "$0")/.."

sudo service mysql start >/dev/null 2>&1 || true

if [ -n "${CODESPACE_NAME:-}" ]; then
  URL="https://${CODESPACE_NAME}-8000.app.github.dev"
  if grep -q '^APP_URL=' .env 2>/dev/null; then
    sed -i "s|^APP_URL=.*|APP_URL=${URL}|" .env
  else
    echo "APP_URL=${URL}" >> .env
  fi
  echo "[vms] public URL: ${URL}/login"
fi

pkill -f "artisan serve" 2>/dev/null || true
sleep 1
nohup php artisan serve --host=0.0.0.0 --port=8000 >/tmp/vms-serve.log 2>&1 &
echo "[vms] artisan serve jalan di port 8000"
