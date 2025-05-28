FROM php:8.1

# Cài extension PHP và netcat để check database
RUN apt-get update && apt-get install -y \
    libonig-dev \
    libzip-dev \
    zip \
    unzip \
    git \
    curl \
    netcat-traditional \
    && docker-php-ext-install pdo_mysql mbstring zip bcmath

# Cài composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Tạo thư mục làm việc
WORKDIR /var/www/html

# Copy entrypoint script
COPY docker-entrypoint.sh /usr/local/bin/
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

# Copy code Laravel
COPY . .

# Cài Laravel dependencies
RUN composer install --no-dev --optimize-autoloader

# Clear cache trong build (để tránh conflict với runtime)
RUN php artisan config:clear && \
    php artisan route:clear && \
    php artisan view:clear && \
    php artisan cache:clear

# Phân quyền
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

# EXPOSE đúng cổng mà Laravel serve
EXPOSE 8000

# Sử dụng entrypoint script
ENTRYPOINT ["docker-entrypoint.sh"]
