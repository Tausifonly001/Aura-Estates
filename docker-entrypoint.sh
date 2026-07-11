#!/bin/bash
set -e

echo "=== Aura Estates Entrypoint ==="
echo "PHP version: $(php -r 'echo PHP_VERSION;')"

# Check and install pgsql extension at runtime if not present
if ! php -m | grep -qi pdo_pgsql; then
    echo "=== pdo_pgsql NOT found — installing at runtime ==="
    
    apt-get update -qq 2>&1 | tail -1
    
    # Install libpq-dev (provides pg_config needed for compilation)
    apt-get install -y -qq --no-install-recommends libpq-dev 2>&1 | tail -3
    
    # Build extensions from source using phpize
    cd /tmp
    
    if ! php -m | grep -qi pgsql; then
        echo "Compiling pgsql extension..."
        pecl install pgsql 2>&1 | tail -5
        echo "extension=pgsql.so" > /usr/local/etc/php/conf.d/pgsql.ini
    fi
    
    echo "Compiling pdo_pgsql extension..."
    pecl install pdo_pgsql 2>&1 | tail -5
    echo "extension=pdo_pgsql.so" > /usr/local/etc/php/conf.d/pdo_pgsql.ini
    
    cd /var/www/html
    
    echo "=== Installed extensions: $(php -m | grep -i pgsql | tr '\n' ' ') ==="
    
    if php -m | grep -qi pdo_pgsql; then
        echo "=== SUCCESS: pdo_pgsql is now available ==="
    else
        echo "=== FAILED: pdo_pgsql could not be installed ==="
    fi
else
    echo "=== pdo_pgsql already available ==="
fi

# Remove stale PID file
rm -f /var/run/apache2/apache2.pid 2>/dev/null || true

# Configure Apache listen port
LISTEN_PORT="${PORT:-80}"
sed -i "s/^Listen .*/Listen ${LISTEN_PORT}/" /etc/apache2/ports.conf

# Update VirtualHost ports
for conf in /etc/apache2/sites-enabled/*.conf; do
    if grep -q "^<VirtualHost \*:" "$conf" 2>/dev/null; then
        sed -i "s/^<VirtualHost \*:[0-9]*>/<VirtualHost *:${LISTEN_PORT}>/g" "$conf"
    elif grep -q "^<VirtualHost \*>" "$conf" 2>/dev/null; then
        sed -i "s/^<VirtualHost \*>/<VirtualHost *:${LISTEN_PORT}>/g" "$conf"
    fi
done

# Run database migrations
echo "=== Running database migrations ==="
php /var/www/html/database/migrate.php 2>&1 || echo "Migration warning (non-fatal)"

# Set proper permissions
chown -R www-data:www-data /var/www/html/uploads /var/www/html/storage 2>/dev/null || true

echo "=== Starting Apache on port ${LISTEN_PORT} ==="
exec apache2ctl -D FOREGROUND
