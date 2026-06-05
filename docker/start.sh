#!/bin/sh
set -e

echo "=== Golden Bird CRM V7.2 — Render Deploy ==="

# Render sets PORT dynamically — default to 8080
export PORT=${PORT:-8080}
echo "→ Listening on port $PORT"

# Ensure SQLite file exists + permissions (fallback if MySQL not configured)
mkdir -p /var/www/html/database
touch /var/www/html/database/database.sqlite
chmod 775 /var/www/html/database/database.sqlite
chown www-data:www-data /var/www/html/database/database.sqlite

# Generate app key if not set
php artisan key:generate --force 2>/dev/null || true

# Laravel bootstrap (run as root, then fix ownership for www-data)
php artisan config:cache
php artisan route:cache
php artisan view:cache
chown -R www-data:www-data bootstrap/cache storage

# Run migrations
echo "→ Running migrations..."
php artisan migrate --force
echo "✔ Migrations done"

# Seed if tables are empty (idempotent — seeder checks before inserting)
echo "→ Running seeders..."
php artisan db:seed --force
echo "✔ Seed done"

# Schedule: run via artisan schedule:run via cron (optional)
# Add to crontab: * * * * * cd /var/www/html && php artisan schedule:run

# Inject PORT into nginx config
envsubst '${PORT}' < /etc/nginx/http.d/default.conf.template > /etc/nginx/http.d/default.conf
echo "✔ Nginx configured for port $PORT"

# Start PHP-FPM in background
php-fpm -D
echo "✔ PHP-FPM started"

# Start nginx in foreground
echo "✔ Starting nginx on port $PORT"
exec nginx -g 'daemon off;'
