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

# Wait for the database to become available.
# The app service already depends_on db with condition: service_healthy,
# so no extra polling is needed here.

php artisan migrate --force

exec "$@"