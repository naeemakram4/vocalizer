#!/bin/sh

# Set Laravel logging to stderr for Render
export LOG_CHANNEL=stderr

# Clear and cache configuration, routes, and views
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear

php artisan config:cache
php artisan route:cache
php artisan view:cache

# Execute the main Apache command
exec apache2-foreground
