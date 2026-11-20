# syntax=docker/dockerfile:1

# ---- Base: PHP 8.4 with Nginx + PHP-FPM, as an unprivileged user ----
FROM serversideup/php:8.4-fpm-nginx AS base

USER root
RUN install-php-extensions bcmath gd intl
USER www-data

# ---- Build: PHP dependencies and front-end assets ----
FROM base AS build

USER root
COPY --from=node:22-bookworm-slim /usr/local/bin/node /usr/local/bin/node
COPY --from=node:22-bookworm-slim /usr/local/lib/node_modules /usr/local/lib/node_modules
RUN ln -s /usr/local/lib/node_modules/npm/bin/npm-cli.js /usr/local/bin/npm
USER www-data

WORKDIR /var/www/html

COPY --chown=www-data:www-data composer.json composer.lock ./
RUN composer install --no-dev --no-scripts --no-autoloader --no-interaction --no-progress

COPY --chown=www-data:www-data package.json package-lock.json ./
RUN npm ci --no-audit --no-fund

COPY --chown=www-data:www-data . .
RUN composer dump-autoload --optimize --no-dev \
    && npm run build \
    && rm -rf node_modules

# ---- App: the image that runs ----
FROM base AS app

ENV PHP_OPCACHE_ENABLE=1 \
    AUTORUN_ENABLED=true \
    AUTORUN_LARAVEL_MIGRATION_ISOLATION=false

COPY --from=build --chown=www-data:www-data /var/www/html /var/www/html
