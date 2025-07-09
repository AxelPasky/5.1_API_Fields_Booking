#!/bin/sh

echo "Starting application setup..."

# Esegui le migrazioni
echo "Running migrations..."
php artisan migrate --force

# TEMPORANEO: Re-seed Passport
echo "Re-seeding Passport clients..."
php artisan db:seed --class=PassportSeeder --force

# Genera le chiavi di Passport
echo "Generating Passport keys..."
php artisan passport:keys --force

# Installa Passport (crea i client se non esistono)
echo "Installing Passport..."
php artisan passport:install --force

# Pulisci la cache
echo "Clearing cache..."
php artisan config:clear
php artisan route:clear
php artisan cache:clear

# Avvia il server
PORT="${PORT:-8080}"
echo "Starting Laravel server on port $PORT..."
exec php artisan serve --host=0.0.0.0 --port="$PORT"