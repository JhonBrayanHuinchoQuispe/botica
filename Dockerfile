FROM php:8.2-cli

# Install system packages and PHP extensions required by Laravel
RUN apt-get update && apt-get install -y \
    git unzip libpng-dev libzip-dev libonig-dev libxml2-dev \
 && docker-php-ext-configure gd --with-jpeg \
 && docker-php-ext-install pdo_mysql gd mbstring zip xml \
 && rm -rf /var/lib/apt/lists/*

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /app

# Copy manifest first for better build cache, then install dependencies
COPY composer.json composer.lock /app/
RUN composer install --no-dev --prefer-dist --no-interaction --optimize-autoloader

# Copy application code
COPY . /app

# Ensure Laravel writable directories have the right permissions
RUN chmod -R 775 storage bootstrap/cache || true

# Render exposes a dynamic port via the PORT environment variable
ENV PORT=8080
EXPOSE 8080

# Use PHP's built-in server for a simple, demo-friendly runtime
# For production hardening consider Nginx + PHP-FPM instead.
CMD php -d variables_order=EGPCS -S 0.0.0.0:${PORT} -t public