#!/bin/bash
# Production deploy script for Catch Jiu Jitsu Portal

set -e

echo "Building React portal..."
cd "$(dirname "$0")"
VITE_API_URL= npm run build

echo "Optimizing Laravel..."
cd laravel
php artisan config:cache
php artisan route:cache
php artisan view:cache 2>/dev/null || true

echo "Done. Restart PHP-FPM or your web server if needed."
