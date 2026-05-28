#!/bin/bash
# Attendora — Production Deployment Script
# Run this after deploying to clear caches and set up the environment.

set -e

echo "🚀 Attendora Production Deploy"
echo "================================"

echo "1/7 Clearing caches..."
php artisan optimize:clear

echo "2/7 Clearing config..."
php artisan config:clear

echo "3/7 Clearing routes..."
php artisan route:clear

echo "4/7 Clearing views..."
php artisan view:clear

echo "5/7 Linking storage..."
php artisan storage:link 2>/dev/null || echo "   (symlink already exists)"

echo "6/7 Running migrations..."
php artisan migrate --force

echo "7/7 Optimizing for production..."
php artisan optimize

echo ""
echo "✅ Deployment complete!"
echo "   Admin login: admin@gmail.com / password"
