#!/bin/sh
set -e

echo "=== [Sahabat Sekolah] Memulai Inisialisasi Kontainer ==="

# 1. Kelola .env persisten di volume env_data
if [ ! -f /var/www/html/env_data/.env ]; then
    echo "[Init] Berkas .env belum ada di env_data, menyalin dari .env.example..."
    cp /var/www/html/.env.example /var/www/html/env_data/.env
fi
ln -sf /var/www/html/env_data/.env /var/www/html/.env

# 2. Generate APP_KEY jika belum terisi
if grep -q "APP_KEY=$" /var/www/html/.env; then
    echo "[Init] Menghasilkan APP_KEY..."
    php artisan key:generate --force
fi

# 3. Sinkronisasi aset statis publik ke volume shared Nginx
echo "[Init] Sinkronisasi aset statis ke shared_public..."
cp -aT /var/www/html/public /var/www/html/public_shared
ln -snf /var/www/html/storage/app/public /var/www/html/public_shared/storage

# 4. Atur kepemilikan dan hak akses
echo "[Init] Mengatur kepemilikan dan izin direktori storage/cache..."
chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache /var/www/html/env_data
chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# 5. Buat direktori log supervisor jika belum ada
mkdir -p /var/log/supervisor

# 6. Jalankan migrasi database otomatis (termasuk tabel jobs & failed_jobs)
echo "[Init] Menjalankan migrasi database..."
php artisan migrate --force || echo "[Warning] Migrasi ditunda/database belum siap menerima koneksi."

# 7. Sinyal restart queue untuk memastikan worker menjalankan kode terbaru setelah repull
echo "[Init] Mengirim sinyal queue:restart..."
php artisan queue:restart || true

echo "=== [Sahabat Sekolah] Inisialisasi Selesai, Menjalankan Supervisor (PHP-FPM + Queue Worker) ==="
exec /usr/bin/supervisord -n -c /etc/supervisor/conf.d/supervisord.conf
