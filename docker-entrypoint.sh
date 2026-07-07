#!/bin/bash
set -e

# Ensure pgsql PDO driver is available
if ! php -m | grep -qi pdo_pgsql; then
    echo "pdo_pgsql not found, installing at runtime..."
    apt-get update -qq > /dev/null 2>&1
    apt-get install -y -qq php-pgsql libpq-dev > /dev/null 2>&1 || \
    apt-get install -y -qq php8.2-pgsql libpq-dev > /dev/null 2>&1 || \
    (pecl install pgsql && echo "extension=pgsql.so" >> /usr/local/etc/php/conf.d/pgsql.ini && \
     pecl install pdo_pgsql && echo "extension=pdo_pgsql.so" >> /usr/local/etc/php/conf.d/pdo_pgsql.ini) || \
    echo "WARNING: Could not install pgsql driver"
    echo "PDO drivers: $(php -m | grep -i pdo | tr '\n' ' ')"
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
