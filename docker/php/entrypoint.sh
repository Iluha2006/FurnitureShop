#!/bin/sh
set -e

# Seed the shared code volume on first boot, so both app and nginx see the
# same files (including the built frontend assets in public/build).
if [ ! -f /var/www/html/artisan ]; then
    echo "Seeding application code into shared volume..."
    cp -a /srv/app/. /var/www/html/
    chown -R www-data:www-data /var/www/html
    echo "Application code seeded."
fi

# Writable dirs for php-fpm (www-data)
chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

# Public storage symlink
php artisan storage:link >/dev/null 2>&1 || true

# Wait for the database to become available
i=0
until php artisan db:show >/dev/null 2>&1; do
    i=$((i + 1))
    if [ "$i" -ge 30 ]; then
        echo "Database is not reachable after 60s, continuing anyway."
        break
    fi
    echo "Waiting for database..."
    sleep 2
done

php artisan migrate --force

exec "$@"