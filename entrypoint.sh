#!/bin/sh

# Attendi che il database sia pronto
echo "Waiting for database connection..."
while ! nc -z -w 2 "$MYSQLHOST" "$MYSQLPORT"; do
  echo "Attempting to connect to MySQL at $MYSQLHOST:$MYSQLPORT..."
  sleep 1
done

echo "Database is up - running migrations..."

# Esegui le migrazioni
php artisan migrate:fresh --seed --force

echo "Migrations finished. Starting Laravel server..."

# Avvia il server Laravel
php artisan serve --host=0.0.0.0 --port=8080