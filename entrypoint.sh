#!/bin/sh

# Imposta un timeout di 60 secondi
TIMEOUT=60

# Attendi che la connessione al database sia disponibile
echo "Waiting for database connection..."
until nc -z -w 2 "$MYSQLHOST" "$MYSQLPORT"; do
  TIMEOUT=$((TIMEOUT-1))
  if [ $TIMEOUT -eq 0 ]; then
    echo "Database connection timed out."
    exit 1
  fi
  sleep 1
done

echo "Database is up - executing migrations..."

# Esegui le migrazioni e i seeder
php artisan config:clear
php artisan migrate:fresh --seed --force

echo "Migrations finished. Starting server."

# Esegui il comando originale del Dockerfile (php-fpm)
exec "$@"