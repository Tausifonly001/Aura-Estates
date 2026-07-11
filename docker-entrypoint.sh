#!/bin/bash
set -e

# Install pgsql extension at runtime if not present
if ! php -m | grep -qi pdo_pgsql; then
    echo "=== pdo_pgsql not found — installing at runtime ==="
    
    # Install build dependencies
    apt-get update -qq > /dev/null 2>&1
    apt-get install -y -qq --no-install-recommends \
        libpq-dev \
        autoconf \
        g++ \
        make \
        > /dev/null 2>&1
    
    # Install pgsql and pdo_pgsql extensions via pecl
    if ! php -m | grep -qi pgsql; then
        echo "Installing pgsql..."
        pecl install pgsql > /dev/null 2>&1
        echo "extension=pgsql.so" > /usr/local/etc/php/conf.d/pgsql.ini
    fi
    
    echo "Installing pdo_pgsql..."
    pecl install pdo_pgsql > /dev/null 2>&1
    echo "extension=pdo_pgsql.so" > /usr/local/etc/php/conf.d/pdo_pgsql.ini
    
    # Clean up build deps
    apt-get purge -y -qq autoconf g++ make > /dev/null 2>&1 || true
    apt-get autoremove -y -qq > /dev/null 2>&1 || true
    rm -rf /var/lib/apt/lists/*
    
    echo "=== PDO drivers now: $(php -m | grep -i pdo | tr '\n' ' ') ==="
    
    if php -m | grep -qi pdo_pgsql; then
        echo "=== pdo_pgsql installed successfully ==="
    else
        echo "=== WARNING: pdo_pgsql installation failed ==="
    fi
fi

rm -f /var/run/apache2/apache2.pid 2>/dev/null || true

LISTEN_PORT="${PORT:-80}"
sed -i "s/^Listen .*/Listen ${LISTEN_PORT}/" /etc/apache2/ports.conf

for conf in /etc/apache2/sites-enabled/*.conf; do
    if grep -q "^<VirtualHost \*:" "$conf" 2>/dev/null; then
        sed -i "s/^<VirtualHost \*:[0-9]*>/<VirtualHost *:${LISTEN_PORT}>/g" "$conf"
    elif grep -q "^<VirtualHost \*>" "$conf" 2>/dev/null; then
        sed -i "s/^<VirtualHost \*>/<VirtualHost *:${LISTEN_PORT}>/g" "$conf"
    fi
done

php /var/www/html/database/migrate.php 2>&1 || true

exec apache2ctl -D FOREGROUND
