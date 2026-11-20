FROM php:8.4-apache

RUN apt-get update && apt-get install -y --no-install-recommends libpng-dev libjpeg62-turbo-dev libwebp-dev unzip \
    && docker-php-ext-configure gd --with-jpeg --with-webp \
    && docker-php-ext-install gd \
    && a2enmod rewrite \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
ENV APACHE_DOCUMENT_ROOT=/var/www/html/public
RUN sed -ri 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf \
    && sed -ri 's!AllowOverride None!AllowOverride All!g' /etc/apache2/apache2.conf

WORKDIR /var/www/html
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-scripts --no-autoloader --prefer-dist
COPY . .
RUN composer dump-autoload --optimize \
    && mkdir -p /data/uploads \
    && rm -rf public/uploads && ln -s /data/uploads public/uploads \
    && chmod 755 /var/www/html \
    && chown -R www-data:www-data storage bootstrap/cache database /data

# Database, uploads and the generated .env all live in /data so they survive upgrades.
ENV DB_DATABASE=/data/database.sqlite
COPY docker-entrypoint.sh /usr/local/bin/plaudit-entrypoint
ENTRYPOINT ["plaudit-entrypoint"]
CMD ["apache2-foreground"]
