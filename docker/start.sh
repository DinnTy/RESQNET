#!/bin/sh
set -e

cd /var/www/html

# Sesuaikan port Apache dengan PORT dari Render
PORT="${PORT:-10000}"
sed -i "s/Listen 80/Listen ${PORT}/g" /etc/apache2/ports.conf
sed -i "s/<VirtualHost \*:80>/<VirtualHost *:${PORT}>/g" /etc/apache2/sites-available/000-default.conf

php artisan package:discover --ansi
php artisan storage:link 2>/dev/null || true

php artisan config:cache
php artisan route:cache
php artisan view:cache

if [ "$RUN_MIGRATIONS" = "true" ]; then
    php artisan migrate --force
fi

if [ "$RUN_DEMO_SEED" = "true" ]; then
    php artisan db:seed --class=DemoDataSeeder --force || echo "Seeder dilewati/gagal"
fi

chown -R www-data:www-data storage bootstrap/cache

exec apache2-foreground
