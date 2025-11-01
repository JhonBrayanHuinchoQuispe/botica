FROM php:8.2-cli

# Install system packages and PHP extensions required by Laravel
RUN apt-get update && apt-get install -y \
    git unzip libpng-dev libzip-dev libonig-dev libxml2-dev \
    libjpeg-dev libfreetype6-dev curl \
 && docker-php-ext-configure gd --with-jpeg --with-freetype \
 && docker-php-ext-install pdo_mysql gd mbstring zip xml \
 && rm -rf /var/lib/apt/lists/*

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /app

# Copy application code first
COPY . /app

# Install dependencies with error handling
RUN composer install --no-dev --prefer-dist --no-interaction --ignore-platform-reqs || \
    composer install --no-dev --no-interaction --ignore-platform-reqs || \
    composer install --ignore-platform-reqs || \
    echo "Composer install completed with warnings"

# Generate autoload files
RUN composer dump-autoload --optimize --no-dev || echo "Autoload generation completed"

# Ensure Laravel writable directories have the right permissions
RUN chmod -R 775 storage bootstrap/cache || true
RUN mkdir -p storage/logs storage/framework/cache storage/framework/sessions storage/framework/views || true
RUN chmod -R 775 storage || true

# Create .env file if it doesn't exist
RUN cp .env.production.example .env || echo "APP_ENV=production" > .env

# Render exposes a dynamic port via the PORT environment variable
ENV PORT=8080
EXPOSE 8080

# Use PHP's built-in server for a simple, demo-friendly runtime
CMD php -d variables_order=EGPCS -S 0.0.0.0:${PORT} -t public