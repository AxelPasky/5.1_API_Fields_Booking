#!/bin/sh

# Esegui le migrazioni e i seeder
php artisan config:clear
php artisan migrate:fresh --seed --force

# Esegui il comando originale del Dockerfile (php-fpm)
exec "$@"