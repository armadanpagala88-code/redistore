#!/bin/bash

# Clear Cache
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Create storage link if not exists
php artisan storage:link

# Setup SQLite database if needed and fix permissions
touch database/database.sqlite
chown -R www-data:www-data database
chmod -R 775 database

# Create upload directories and set permissions
mkdir -p public/uploads/profil public/uploads/artikel public/uploads/bukti public/images/banners
chown -R www-data:www-data public/uploads public/images
chmod -R 775 public/uploads public/images

# Run migrations automatically
php artisan migrate --force

# Start PHP-FPM in background
php-fpm -D

# Start Nginx in foreground
nginx -g 'daemon off;'
