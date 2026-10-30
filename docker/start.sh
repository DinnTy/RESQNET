#!/bin/sh
# Skrip start RESQNET: siapkan Laravel lalu jalankan Apache.
set -e
cd /var/www/html

php artisan package:discover --ansi || true
php artisan storage:link 2>/dev/null || true

# Cache konfigurasi untuk produksi (APP_KEY dan DB_* dibaca dari environment variable)
php artisan config:cache
php artisan route:cache || true
php artisan view:cache || true

# Jalankan migrasi hanya bila diminta (RUN_MIGRATIONS=true). Aman diulang.
if [ "$RUN_MIGRATIONS" = "true" ]; then
  php artisan migrate --force
fi
# Isi data demo hanya bila diminta (RUN_DEMO_SEED=true).
if [ "$RUN_DEMO_SEED" = "true" ]; then
  php artisan db:seed --class=DemoDataSeeder --force || echo "Seeder dilewati/gagal"
fi

chown -R www-data:www-data storage bootstrap/cache
exec apache2-foreground
