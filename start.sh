#!/bin/sh

echo "Starting application setup..."

# Esegui le migrazioni (usa 'migrate' invece di 'migrate:fresh' per non perdere dati)
echo "Running migrations..."
php artisan migrate --force

# Pulisci la cache
echo "Clearing cache..."
php artisan config:clear
php artisan route:clear#!/bin/sh

echo "Starting application setup..."

# Esegui le migrazioni
echo "Running migrations..."
php artisan migrate --force

# TEMPORANEO: Re-seed Passport
echo "Re-seeding Passport clients..."
php artisan db:seed --class=PassportSeeder --force

# Pulisci la cache
echo "Clearing cache..."
php artisan config:clear
php artisan route:clear
php artisan passport:client --personal --no-interaction || true

# Avvia il server
PORT="${PORT:-8080}"
echo "Starting Laravel server on port $PORT..."
exec php artisan serve --host=0.0.0.0 --port="$PORT"

# Avvia il server
PORT="${PORT:-8080}"
echo "Starting Laravel server on port $PORT..."
exec php artisan serve --host=0.0.0.0 --port="$PORT"