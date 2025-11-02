FROM php:8.3-fpm

WORKDIR /app

# Install required PHP extensions
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libicu-dev \
    libzip-dev \
    && docker-php-ext-install -j$(nproc) \
    intl \
    zip \
    opcache \
    && pecl install mongodb \
    && docker-php-ext-enable mongodb \
    && rm -rf /var/lib/apt/lists/*

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

COPY --link \
    --from=ghcr.io/symfony-cli/symfony-cli:latest \
    /usr/local/bin/symfony /usr/local/bin/symfony
    
# PHP configuration
RUN echo "memory_limit = 256M" > /usr/local/etc/php/conf.d/memory.ini \
    && echo "upload_max_filesize = 64M" >> /usr/local/etc/php/conf.d/upload.ini \
    && echo "post_max_size = 64M" >> /usr/local/etc/php/conf.d/upload.ini

# Create cache directories with proper permissions
RUN mkdir -p /app/var/cache/dev/doctrine/odm/mongodb/Proxies \
    && mkdir -p /app/var/cache/dev/doctrine/odm/mongodb/Hydrators \
    && mkdir -p /app/var/cache/prod/doctrine/odm/mongodb/Proxies \
    && mkdir -p /app/var/cache/prod/doctrine/odm/mongodb/Hydrators \
    && mkdir -p /app/var/log \
    && mkdir -p /app/config/jwt \
    && chown -R www-data:www-data /app/var \
    && chmod -R 775 /app/var

# Create entrypoint script to fix permissions
COPY docker-entrypoint.sh /usr/local/bin/
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

# PHP-FPM listens on port 9000 (default)

ENTRYPOINT ["docker-entrypoint.sh"]
CMD ["php-fpm"]