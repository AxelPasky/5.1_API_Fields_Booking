#!/bin/sh

echo "Starting Laravel server..."

# Pulisci la cache per sicurezza
php artisan config:clear
php artisan route:clear

# Avvia il server
echo "Starting server on port ${PORT:-8080}..."
exec php artisan serve --host=0.0.0.0 --port=${PORT:-8080}