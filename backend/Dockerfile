# Multi-stage build for Laravel 12 application

# Stage 1: Base image with PHP extensions
FROM php:8.2-fpm-alpine AS base

# Install system dependencies
RUN apk add --no-cache \
    git \
    curl \
    libpng-dev \
    oniguruma-dev \
    libxml2-dev \
    zip \
    unzip \
    libzip-dev \
    libjpeg-turbo-dev \
    freetype-dev \
    libmcrypt-dev \
    imagemagick-dev \
    supervisor \
    nginx

# Install PHP extensions
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
    gd \
    pdo_mysql \
    zip \
    exif \
    pcntl \
    bcmath \
    opcache \
    imagick

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www/html

# Stage 2: Install dependencies
FROM base AS deps

# Copy composer files
COPY composer.json composer.lock ./

# Install dependencies
RUN composer install --no-dev --optimize-autoloader --no-interaction

# Stage 3: Development image
FROM base AS development

# Install development dependencies
COPY composer.json composer.lock ./
RUN composer install --optimize-autoloader --no-interaction

# Copy application code
COPY . .

# Set permissions
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html/storage \
    && chmod -R 755 /var/www/html/bootstrap/cache

# Copy PHP configuration
COPY ../docker/php/php.ini /usr/local/etc/php/conf.d/custom.ini
COPY ../docker/php/php-fpm.conf /usr/local/etc/php-fpm.d/www.conf

# Copy supervisord configuration
COPY ../docker/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

EXPOSE 9000

CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"]

# Stage 4: Production image
FROM base AS production

# Copy dependencies from deps stage
COPY --from=deps /var/www/html/vendor ./vendor

# Copy application code
COPY . .

# Optimize for production
RUN php artisan config:cache \
    && php artisan route:cache \
    && php artisan view:cache \
    && php artisan event:cache

# Set permissions
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html/storage \
    && chmod -R 755 /var/www/html/bootstrap/cache

# Copy PHP configuration
COPY ../docker/php/php.ini /usr/local/etc/php/conf.d/custom.ini
COPY ../docker/php/php-fpm.conf /usr/local/etc/php-fpm.d/www.conf

# Copy supervisord configuration
COPY ../docker/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# Health check
HEALTHCHECK --interval=30s --timeout=3s --start-period=5s --retries=3 \
  CMD curl -f http://localhost:9000/health || exit 1

EXPOSE 9000

CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"]
