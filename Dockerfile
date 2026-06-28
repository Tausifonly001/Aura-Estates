FROM debian:bookworm-slim

RUN apt-get update && apt-get install -y --no-install-recommends \
    apache2 \
    ca-certificates \
    curl \
    libapache2-mod-php8.2 \
    php8.2 \
    php8.2-mysqli \
    php8.2-pdo \
    php8.2-mysql \
    php8.2-gd \
    php8.2-zip \
    php8.2-mbstring \
    php8.2-curl \
    php8.2-xml \
    unzip \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

RUN a2dismod mpm_event && a2enmod mpm_prefork rewrite

COPY . /var/www/html/

RUN chown -R www-data:www-data /var/www/html/uploads && \
    chmod -R 755 /var/www/html/uploads /var/www/html/storage

RUN cp /var/www/html/.env.example /var/www/html/.env

ENV COMPOSER_ALLOW_SUPERUSER=1
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer && \
    cd /var/www/html && composer install --optimize-autoloader --no-dev

EXPOSE 80

CMD ["apache2ctl", "-D", "FOREGROUND"]