FROM php:8.2-apache

RUN rm -rf /etc/apache2/mods-enabled/mpm_event.* /etc/apache2/mods-available/mpm_event.* 2>/dev/null; a2enmod rewrite

RUN apt-get update && apt-get install -y unzip libzip-dev libpng-dev libjpeg-dev libfreetype6-dev && \
    docker-php-ext-configure gd --with-freetype --with-jpeg && \
    docker-php-ext-install mysqli pdo pdo_mysql zip gd && \
    apt-get clean && rm -rf /var/lib/apt/lists/*

COPY . /var/www/html/

RUN chown -R www-data:www-data /var/www/html/uploads && \
    chmod -R 755 /var/www/html/uploads /var/www/html/storage

RUN cp /var/www/html/.env.example /var/www/html/.env

ENV COMPOSER_ALLOW_SUPERUSER=1
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer && \
    cd /var/www/html && composer install --optimize-autoloader --no-dev

EXPOSE 80

CMD ["apache2-foreground"]
