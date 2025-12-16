FROM php:8.2-cli

# Install system dependencies and PHP extensions
RUN apt-get update \
    && apt-get install -y --no-install-recommends \
       libzip-dev \
       unzip \
    && docker-php-ext-install pdo pdo_mysql zip \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Copy composer files first
COPY composer.json composer.lock* ./

# Install dependencies
RUN composer install --no-interaction --no-dev --optimize-autoloader || true

# Copy rest of application
COPY . .

# Create uploads directories
RUN mkdir -p /var/www/html/uploads/payments /var/www/html/uploads/tracking \
    && chmod -R 755 /var/www/html/uploads

# Create symlink for images directory
RUN ln -sf /var/www/html/images /var/www/html/web/images || true

# Use PORT environment variable for Railway
ENV PORT=8000
EXPOSE 8000

# Copy optimized PHP configuration
COPY php.ini /usr/local/etc/php/conf.d/99-performance.ini

# Use shell form to properly expand PORT variable
CMD ["sh", "-c", "test -f vendor/autoload.php || composer install --no-interaction --no-dev --prefer-dist --optimize-autoloader; php -S 0.0.0.0:${PORT} -t web"]
