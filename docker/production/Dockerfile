# Dockerfile
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

# Copy application code and scripts
COPY . .

# Install Composer dependencies
RUN composer install --optimize-autoloader --no-dev

# Install npm dependencies and build Vue 3 assets
#RUN npm install && npm run build

# Set permissions for Laravel
RUN chown -R www-data:www-data /var/www/perfectorder \
    && chmod -R 755 /var/www/perfectorder/storage \
    && chmod -R 755 /var/www/perfectorder/bootstrap/cache \
    && chmod +x /var/www/perfectorder/docker-start.sh

# Copy Nginx configuration
COPY nginx/default.conf /etc/nginx/conf.d/default.conf

# Expose port 80
EXPOSE 80

# Use startup script
ENTRYPOINT ["/var/www/perfectorder/docker-start.sh"]
