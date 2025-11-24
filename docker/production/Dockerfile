# docker/production/Dockerfile
FROM php:8.4-fpm-alpine

# Install system dependencies
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
    libmagickwand-dev \
    imagemagick \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
        pdo_mysql \
        gd \
        mbstring \
        exif \
        pcntl \
        bcmath \
        xml \
        zip \
    && pecl install imagick \
    && docker-php-ext-enable imagick \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Install Composer
COPY --from=composer:2.9.2 /usr/bin/composer /usr/local/bin/composer

# Set working directory
WORKDIR /var/www/perfectorder.shop

# Copy only dependency definitions first (maximize layer caching)
COPY composer.json composer.lock ./
COPY package.json package-lock.json ./

# Install PHP dependencies (production only)
RUN composer install --no-dev --no-scripts --no-autoloader --prefer-dist --optimize-autoloader

# Install npm dependencies and build Vue 3 assets (100% reliable than just: npm ci && npm run build)
RUN npm ci

# Copy application code
COPY . .

RUN npm run build

# Set permissions for Laravel
RUN chown -R www-data:www-data /var/www/perfectorder.shop \
    && chmod -R 755 /var/www/perfectorder.shop/storage \
    && chmod -R 755 /var/www/perfectorder.shop/bootstrap/cache

# Copy Nginx configuration
COPY docker/production/nginx/default.conf /etc/nginx/conf.d/default.conf

# Copy entrypoint
COPY docker/production/entrypoint.sh /usr/local/bin/entrypoint.sh

# Make entrypoint executable
RUN chmod +x /usr/local/bin/entrypoint.sh

# Use the new entrypoint
ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
