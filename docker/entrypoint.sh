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

# Run migrations automatically
php artisan migrate --force

# Start PHP-FPM in background
php-fpm -D

# Start Nginx in foreground
nginx -g 'daemon off;'
