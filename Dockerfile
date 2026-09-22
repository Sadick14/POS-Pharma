FROM php:8.3-cli-alpine

RUN apk add --no-cache \
    sqlite \
    sqlite-dev \
    libpng-dev \
    libzip-dev \
    zip \
    unzip \
    curl \
    icu-dev \
    oniguruma-dev \
    nodejs \
    npm

RUN docker-php-ext-install \
    pdo \
    pdo_sqlite \
    pdo_mysql \
    bcmath \
    gd \
    zip \
    intl \
    opcache \
    pcntl

COPY --from=composer:2.7 /usr/bin/composer /usr/bin/composer

WORKDIR /app

# Copy project files
COPY . /app

# Install PHP production dependencies
RUN composer install --no-dev --optimize-autoloader --no-interaction

# Prepare storage & cache directories with write permissions
RUN mkdir -p /app/storage/framework/cache/data \
    /app/storage/framework/sessions \
    /app/storage/framework/views \
    /app/storage/logs \
    /app/bootstrap/cache \
    /app/database \
 && chmod -R 777 /app/storage /app/bootstrap/cache /app/database

# Copy and setup entrypoint script
COPY docker-entrypoint.sh /usr/local/bin/docker-entrypoint.sh
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

EXPOSE 8000

CMD ["docker-entrypoint.sh"]
