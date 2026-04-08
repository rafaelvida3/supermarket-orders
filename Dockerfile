FROM php:8.4-cli AS php_base

WORKDIR /var/www

RUN apt-get update && apt-get install -y \
    git \
    curl \
    unzip \
    libpng-dev \
    libxml2-dev \
    libzip-dev \
    libpq-dev \
    libonig-dev \
    netcat-openbsd \
    postgresql-client \
    && docker-php-ext-install pdo pdo_pgsql mbstring exif pcntl bcmath gd zip \
    && curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

FROM php_base AS dev
COPY composer.json composer.lock* ./
RUN composer install --no-interaction --prefer-dist --no-progress --no-scripts
CMD ["php", "-v"]

FROM node:20 AS frontend_builder
WORKDIR /app
COPY package*.json ./
RUN npm ci
COPY . .
RUN npm run build

FROM php_base AS production
COPY . .
COPY --from=frontend_builder /app/public/build /var/www/public/build

RUN mkdir -p database storage/framework/cache storage/framework/sessions storage/framework/views bootstrap/cache \
    && composer install --no-interaction --no-dev --prefer-dist --optimize-autoloader --no-progress \
    && chmod -R 775 storage bootstrap/cache database

EXPOSE 10000

CMD ["sh", "-c", "php artisan migrate --force && php artisan products:import && php artisan serve --host=0.0.0.0 --port=${PORT:-10000}"]