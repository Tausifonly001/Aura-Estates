#!/bin/bash
set -e

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

exec apache2ctl -D FOREGROUND
