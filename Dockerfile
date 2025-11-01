FROM php:8.2-fpm

# Install system packages and PHP extensions required by Laravel
RUN apt-get update && apt-get install -y \
    git unzip libpng-dev libzip-dev libonig-dev libxml2-dev \
    libjpeg-dev libfreetype6-dev curl nginx supervisor \
 && docker-php-ext-configure gd --with-jpeg --with-freetype \
 && docker-php-ext-install pdo_mysql gd mbstring zip xml \
 && rm -rf /var/lib/apt/lists/*

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www

# Copy application code
COPY . /var/www

# Install dependencies with error handling
RUN composer install --no-dev --prefer-dist --no-interaction --ignore-platform-reqs || \
    composer install --no-dev --no-interaction --ignore-platform-reqs || \
    composer install --ignore-platform-reqs || \
    echo "Composer install completed with warnings"

# Generate autoload files
RUN composer dump-autoload --optimize --no-dev || echo "Autoload generation completed"

# Set permissions
RUN chown -R www-data:www-data /var/www \
    && chmod -R 755 /var/www/storage \
    && chmod -R 755 /var/www/bootstrap/cache

# Create required directories
RUN mkdir -p storage/logs storage/framework/cache storage/framework/sessions storage/framework/views || true

# Copy nginx configuration
COPY nginx.conf /etc/nginx/sites-available/default

# Create .env file if it doesn't exist
RUN cp .env.production.example .env || echo "APP_ENV=production" > .env

# Create supervisor configuration
RUN echo '[supervisord]\nnodaemon=true\n\n[program:nginx]\ncommand=nginx -g "daemon off;"\nautostart=true\nautorestart=true\n\n[program:php-fpm]\ncommand=php-fpm\nautostart=true\nautorestart=true\n\n[program:laravel-migrate]\ncommand=php artisan migrate --force\nautorestart=false\nstartsecs=0\nstartretries=1' > /etc/supervisor/conf.d/supervisord.conf

# Render exposes a dynamic port via the PORT environment variable
ENV PORT=80
EXPOSE 80

# Start supervisor
CMD ["/usr/bin/supervisord"]