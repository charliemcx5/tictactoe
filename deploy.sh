#!/bin/bash

set -e  # Exit on any error

echo "Starting deployment..."

cd ~/tictactoe

echo "Pulling latest code from main..."
git pull origin main

echo "Installing Composer dependencies (production only)..."
composer install --optimize-autoloader --no-dev --no-interaction

echo "Running database migrations..."
php artisan migrate --force

echo "Clearing and caching configuration/routes/views..."
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo "Installing NPM dependencies (including dev for build)..."
npm ci

echo "Building assets..."
npm run build

echo "Cleaning up dev dependencies..."
npm prune --production

echo "Fixing permissions (group writable – ignore chown errors)..."
# Set group to www-data and make group-writable
chgrp -R www-data storage bootstrap/cache database 2>/dev/null || true
chmod -R 775 storage bootstrap/cache
chmod 664 database/database.sqlite 2>/dev/null || true

# Optional: clear old compiled views if needed
find storage/framework/views -type f -name "*.php" -exec chmod 664 {} \; 2>/dev/null || true

echo "Restarting Reverb..."
sudo supervisorctl restart reverb

echo "Reloading Nginx..."
sudo systemctl reload nginx

echo "Deployment completed successfully! 🚀"
