#!/bin/sh

echo "Starting application setup..."

# Attendi un po' per essere sicuri che MySQL sia pronto
sleep 5

# Esegui le migrazioni
echo "Running migrations..."
php artisan migrate:fresh --seed --force

# Avvia il server
echo "Starting server..."
php artisan serve --host=0.0.0.0 --port=${PORT:-8080}