# docker/development/Dockerfile
FROM php:8.4-fpm

# Install system dependencies, PHP extensions and Node.js
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libonig-dev \
    libxml2-dev \
    libzip-dev \
    zip \
    unzip \
    nginx \
    nodejs \
    npm \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install pdo_mysql gd mbstring exif pcntl bcmath xml zip \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Install Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Set working directory
WORKDIR /var/www/perfectorder

# Copy application code
COPY . .

# Install Composer dependencies (dev mode for development)
RUN composer install --optimize-autoloader

# Install npm dependencies and build Vue 3 assets
RUN npm ci && npm run build

# Set permissions for Laravel
RUN chown -R www-data:www-data /var/www/perfectorder \
    && chmod -R 755 /var/www/perfectorder/storage \
    && chmod -R 755 /var/www/perfectorder/bootstrap/cache

# Copy entrypoint & make it executable
COPY docker/development/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

# Copy Nginx configuration
COPY docker/development/nginx/default.conf /etc/nginx/conf.d/default.conf

# Expose port 80
EXPOSE 80

# Use the new entrypoint
ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
