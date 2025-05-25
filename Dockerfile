# Dockerfile

FROM php:8.1-fpm

# Cài đặt các extension cần thiết
RUN apt-get update && apt-get install -y \
    libonig-dev \
    libzip-dev \
    zip \
    unzip \
    git \
    curl \
    && docker-php-ext-install pdo_mysql mbstring zip bcmath

# Cài Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Tạo thư mục làm việc
WORKDIR /var/www/html

# Copy toàn bộ code vào container
COPY . .

# Cài đặt các package PHP với Composer
RUN composer install --no-dev --optimize-autoloader

# Chỉnh quyền thư mục storage và bootstrap cache
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

# Expose port 9000 cho PHP-FPM
EXPOSE 9000

CMD ["php-fpm"]
