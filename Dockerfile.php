# Stage 1: Build dependencies
FROM composer:2 as dependencies
WORKDIR /app
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-scripts --no-autoloader --ignore-platform-reqs

# Stage 2: Production image
FROM php:8.2-cli-alpine

# Installa dipendenze essenziali in un solo comando per velocizzare
RUN apk add --no-cache \
    postgresql-dev \
    && docker-php-ext-install pdo pdo_mysql \
    && apk del postgresql-dev

# Copia le dipendenze dal primo stage
WORKDIR /app
COPY --from=dependencies /app/vendor vendor/
COPY . .

# Genera l'autoloader ottimizzato
RUN composer dump-autoload --optimize --no-dev

# Copia lo script di avvio
COPY start.sh /app/start.sh
RUN chmod +x /app/start.sh

# Esponi la porta
EXPOSE 8080

# Usa lo script di avvio
CMD ["/app/start.sh"]