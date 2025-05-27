# Stage 1: Cài đặt dependencies với Composer
FROM composer:2 AS composer

# Stage 2: Build image chính
FROM php:8.1

# Cài đặt các gói cần thiết và PHP extensions
RUN apt-get update && apt-get install -y \
    libonig-dev \
    libzip-dev \
    zip \
    unzip \
    git \
    curl \
    && docker-php-ext-install pdo_mysql mbstring zip bcmath \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Copy Composer từ stage composer
COPY --from=composer /usr/bin/composer /usr/bin/composer

# Tạo thư mục làm việc
WORKDIR /var/www/html

# Copy chỉ các file cần thiết của Laravel
COPY composer.json composer.lock ./
COPY app ./app
COPY bootstrap ./bootstrap
COPY config ./config
COPY database ./database
COPY public ./public
COPY resources ./resources
COPY routes ./routes
COPY storage ./storage
COPY artisan ./
COPY .env.example .env

# Cài đặt dependencies Laravel
RUN composer install --no-dev --optimize-autoloader

# Clear và rebuild cache
RUN php artisan config:clear && \
    php artisan route:clear && \
    php artisan view:clear && \
    php artisan cache:clear && \
    php artisan config:cache && \
    php artisan route:cache

# Phân quyền
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

# Expose cổng cho php artisan serve
EXPOSE 8000

# Khởi động Laravel server
CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8000"]
