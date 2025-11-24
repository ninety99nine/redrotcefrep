# docker/development/Dockerfile
FROM php:8.4-fpm-alpine

# Install system dependencies
RUN apk add --no-cache \
    nginx \
    supervisor \
    nodejs \
    npm \
    libpng-dev libjpeg-turbo-dev freetype-dev \
    libzip-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install gd zip \
    && rm -rf /var/cache/apk/*

# Install Composer
COPY --from=composer:2.9.2 /usr/bin/composer /usr/local/bin/composer

# Set working directory
WORKDIR /var/www/perfectorder

# Copy only dependency definitions first (maximize layer caching)
COPY composer.json composer.lock ./
COPY package.json package-lock.json ./

# Install PHP dependencies
RUN composer install --no-scripts --no-autoloader --prefer-dist --optimize-autoloader

# Install npm dependencies and build Vue 3 assets (100% reliable than just: npm ci && npm run build)
RUN npm ci

# Copy application code
COPY . .

RUN npm run build

# Set permissions for Laravel
RUN chown -R www-data:www-data /var/www/perfectorder \
    && chmod -R 755 /var/www/perfectorder/storage \
    && chmod -R 755 /var/www/perfectorder/bootstrap/cache

# Copy Nginx configuration
COPY docker/development/nginx/default.conf /etc/nginx/conf.d/default.conf

# Copy entrypoint
COPY docker/development/entrypoint.sh /usr/local/bin/entrypoint.sh

# Make entrypoint executable
RUN chmod +x /usr/local/bin/entrypoint.sh

# Use the new entrypoint
ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
