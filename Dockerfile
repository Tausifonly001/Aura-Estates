FROM debian:bookworm-slim

RUN apt-get update && apt-get install -y --no-install-recommends \
    apache2 \
    ca-certificates \
    curl \
    libapache2-mod-php8.2 \
    php8.2 \
    php8.2-pgsql \
    php8.2-pdo \
    php8.2-gd \
    php8.2-zip \
    php8.2-mbstring \
    php8.2-curl \
    php8.2-xml \
    unzip \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

RUN a2dismod mpm_event && a2enmod mpm_prefork rewrite headers

RUN sed -i '/<Directory \/var\/www\/>/,/<\/Directory>/ s/AllowOverride None/AllowOverride All/' /etc/apache2/apache2.conf

COPY . /var/www/html/

RUN rm -f /var/www/html/.env /var/www/html/cookies.txt && \
    cp /var/www/html/.env.example /var/www/html/.env && \
    chown -R www-data:www-data /var/www/html/uploads && \
    chmod -R 755 /var/www/html/uploads /var/www/html/storage

ENV COMPOSER_ALLOW_SUPERUSER=1
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer && \
    cd /var/www/html && composer install --optimize-autoloader --no-dev

RUN chmod +x /var/www/html/docker-entrypoint.sh

RUN apache2ctl configtest

EXPOSE 80

ENTRYPOINT ["/var/www/html/docker-entrypoint.sh"]
