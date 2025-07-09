# Stage 1: Build dependencies
FROM composer:2 as dependencies
WORKDIR /app
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-scripts --no-autoloader --ignore-platform-reqs --prefer-dist

# Stage 2: Production image
FROM php:8.2-cli-alpine

# Installa solo le estensioni PHP necessarie
RUN docker-php-ext-install pdo pdo_mysql

# Imposta la directory di lavoro
WORKDIR /app

# Copia prima solo i vendor (layer cacheable)
COPY --from=dependencies /app/vendor vendor/

# Copia solo i file necessari per l'applicazione
COPY app app/
COPY bootstrap bootstrap/
COPY config config/
COPY database database/
COPY public public/
COPY resources resources/
COPY routes routes/
COPY storage storage/
COPY artisan composer.json composer.lock ./

# Copia composer per dump-autoload
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Genera l'autoloader
RUN composer dump-autoload --optimize --no-dev --classmap-authoritative

# Copia e prepara lo script di avvio
COPY start.sh ./
RUN chmod +x start.sh

# Esponi la porta
EXPOSE 8080

# Avvia l'applicazione
CMD ["./start.sh"]