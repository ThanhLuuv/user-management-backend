
FROM php:8.1

# Cài extension PHP
RUN apt-get update && apt-get install -y \
    libonig-dev \
    libzip-dev \
    zip \
    unzip \
    git \
    curl \
    && docker-php-ext-install pdo_mysql mbstring zip bcmath

# Cài composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Tạo thư mục làm việc
WORKDIR /var/www/html

# Copy code Laravel
COPY . .

# Cài Laravel dependencies
RUN composer install --no-dev --optimize-autoloader

# Clear và rebuild cache (QUAN TRỌNG)
# RUN php artisan config:clear && \
#     php artisan route:clear && \
#     php artisan view:clear && \
#     php artisan cache:clear && \
#     php artisan config:cache && \
#     php artisan route:cache

# Phân quyền
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

# EXPOSE đúng cổng mà Laravel serve
EXPOSE 8000

# Dùng php artisan serve để Laravel mở cổng HTTP
CMD php artisan serve --host=0.0.0.0 --port=8000
