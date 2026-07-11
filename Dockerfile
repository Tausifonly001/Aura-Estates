# Cache bust 2026-07-08-v6
FROM php:8.2-apache

RUN apt-get update && apt-get install -y --no-install-recommends \
    libpq-dev \
    unzip \
    && docker-php-ext-install pgsql pdo_pgsql \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

RUN php -m | grep -i pgsql || (echo "pgsql extension MISSING" && exit 1)

RUN a2enmod rewrite headers

RUN sed -i '/<Directory \/var\/www\/>/,/<\/Directory>/ s/AllowOverride None/AllowOverride All/' /etc/apache2/apache2.conf

COPY . /var/www/html/

RUN mkdir -p /var/www/html/uploads /var/www/html/storage && \
    rm -f /var/www/html/.env /var/www/html/cookies.txt && \
    cp /var/www/html/.env.example /var/www/html/.env && \
    chown -R www-data:www-data /var/www/html/uploads /var/www/html/storage && \
    chmod -R 755 /var/www/html/uploads /var/www/html/storage

ENV COMPOSER_ALLOW_SUPERUSER=1
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer && \
    cd /var/www/html && composer install --optimize-autoloader --no-dev

RUN chmod +x /var/www/html/docker-entrypoint.sh

RUN apache2ctl configtest

EXPOSE 80

ENTRYPOINT ["/var/www/html/docker-entrypoint.sh"]
