#!/bin/sh

echo "Starting application setup..."

# Attendi che MySQL sia pronto
sleep 5

# Esegui le migrazioni
echo "Running migrations..."
php artisan migrate:fresh --seed --force

# Pulisci la cache
echo "Clearing cache..."
php artisan config:clear
php artisan route:clear
php artisan cache:clear

# Avvia il server
echo "Starting Laravel server on port ${PORT:-8080}..."
exec php artisan serve --host=0.0.0.0 --port=${PORT:-8080}