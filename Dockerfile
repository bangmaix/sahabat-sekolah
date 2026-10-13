FROM php:8.3-fpm

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libpq-dev \
    zip \
    unzip \
    nodejs \
    npm

# Clear cache
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# Install PHP extensions
RUN docker-php-ext-install pdo_mysql pdo_pgsql mbstring exif pcntl bcmath gd

# Get latest Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www/html

# Copy application files
COPY . .

# Install PHP dependencies
RUN composer install --optimize-autoloader --no-dev

# Install Node dependencies and build assets
RUN npm install && npm run build

# Set permissions
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

# Create a startup script to copy public files, manage persistent .env, and start php-fpm
RUN echo '#!/bin/sh' > /usr/local/bin/start.sh \
    && echo 'if [ ! -f /var/www/html/env_data/.env ]; then cp /var/www/html/.env.example /var/www/html/env_data/.env; fi' >> /usr/local/bin/start.sh \
    && echo 'ln -sf /var/www/html/env_data/.env /var/www/html/.env' >> /usr/local/bin/start.sh \
    && echo 'if grep -q "APP_KEY=$" /var/www/html/.env; then php artisan key:generate --force; fi' >> /usr/local/bin/start.sh \
    && echo 'cp -aT /var/www/html/public /var/www/html/public_shared' >> /usr/local/bin/start.sh \
    && echo 'ln -snf /var/www/html/storage/app/public /var/www/html/public_shared/storage' >> /usr/local/bin/start.sh \
    && echo 'chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache /var/www/html/env_data' >> /usr/local/bin/start.sh \
    && echo 'exec php-fpm' >> /usr/local/bin/start.sh \
    && chmod +x /usr/local/bin/start.sh

EXPOSE 9000
CMD ["/usr/local/bin/start.sh"]
