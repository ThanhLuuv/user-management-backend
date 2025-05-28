#!/bin/bash

# Đợi database sẵn sàng
echo "Waiting for database..."
while ! nc -z mysql 3306; do
  sleep 1
done
echo "Database is ready!"

# Tạo app key nếu chưa có
if [ ! -f .env ] || ! grep -q "APP_KEY=" .env || [ -z "$(grep APP_KEY= .env | cut -d '=' -f2)" ]; then
    echo "Generating application key..."
    php artisan key:generate --force
fi

# Tạo JWT secret nếu chưa có
if ! grep -q "JWT_SECRET=" .env || [ -z "$(grep JWT_SECRET= .env | cut -d '=' -f2)" ]; then
    echo "Generating JWT secret..."
    php artisan jwt:secret --force
fi

# Chạy migration
echo "Running migrations..."
php artisan migrate --force

# Seed dữ liệu (nếu cần)
if [ "$RUN_SEEDER" = "true" ]; then
    echo "Running seeders..."
    php artisan db:seed --force
fi

# Clear cache trước
echo "Clearing cache..."
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan cache:clear

# Rebuild cache sau khi đã clear
echo "Building cache..."
php artisan config:cache
php artisan route:cache

echo "Application is ready!"

# Khởi chạy Laravel
exec php artisan serve --host=0.0.0.0 --port=8000
