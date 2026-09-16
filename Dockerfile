# Qawmi madrasa SaaS — Fly.io image.
#
# Two-stage: node builds the Vite bundle, php-fpm runs the app. The final
# image carries no node_modules and no composer dev dependencies.

# ---------- stage 1: front-end assets ----------
FROM node:22-alpine AS assets

WORKDIR /build

COPY package.json package-lock.json ./
RUN npm ci --ignore-scripts

COPY vite.config.js ./
COPY resources ./resources
RUN npm run build

# ---------- stage 2: application ----------
# 8.4, not 8.3: composer.lock pins Symfony 8.1, which requires php >= 8.4.1.
# Building on 8.3 fails at composer install with seventeen version conflicts.
FROM php:8.4-fpm-alpine

# gd  — mPDF renders the QR codes and any raster logo on a receipt.
# intl — Carbon/HijriDate formatting.
# zip  — composer install from cached archives.
# pdo_sqlite is compiled in by default but sqlite3 dev headers are needed.
# pdo_pgsql — Render/managed Postgres. Without it DB_CONNECTION=pgsql dies
# at boot with "could not find driver", which is not obvious from the logs.
RUN apk add --no-cache \
        nginx supervisor \
        freetype libjpeg-turbo libpng libwebp icu-libs sqlite-libs libzip \
        libpq \
    && apk add --no-cache --virtual .build-deps \
        $PHPIZE_DEPS freetype-dev libjpeg-turbo-dev libpng-dev libwebp-dev \
        icu-dev sqlite-dev libzip-dev postgresql-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp \
    && docker-php-ext-install -j"$(nproc)" gd intl zip pdo_sqlite pdo_pgsql opcache \
    && apk del .build-deps

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Composer first, with only the manifests — this layer is rebuilt only when
# a dependency actually changes, not on every code edit.
COPY composer.json composer.lock ./
RUN composer install \
        --no-dev --no-scripts --no-autoloader \
        --prefer-dist --no-interaction --no-progress

COPY . .

# .dockerignore drops the framework cache directories (they hold local dev
# state), and a directory excluded there is absent from the image entirely,
# not merely empty. Blade's compiler resolves view.compiled at boot, so
# without storage/framework/views the very next line dies with
# "Please provide a valid cache path" — package:discover boots the framework.
# The Fly volume replaces storage/ at runtime; this is only for build time.
RUN mkdir -p \
        storage/framework/cache/data \
        storage/framework/sessions \
        storage/framework/views \
        storage/app/public \
        storage/logs

RUN composer dump-autoload --optimize --no-dev --classmap-authoritative

COPY --from=assets /build/public/build ./public/build

# Bengali fonts are shipped code (PdfFactory reads base_path('storage/fonts')),
# but /var/www/html/storage is replaced by the Fly volume at runtime. Keep a
# copy outside the mount point and restore it on boot — see entrypoint.sh.
RUN cp -r storage/fonts /usr/local/share/qawmi-fonts

COPY deploy/nginx.conf       /etc/nginx/nginx.conf
COPY deploy/php-fpm.conf     /usr/local/etc/php-fpm.d/zz-app.conf
COPY deploy/php.ini          /usr/local/etc/php/conf.d/zz-app.ini
COPY deploy/supervisord.conf /etc/supervisord.conf
COPY deploy/entrypoint.sh    /usr/local/bin/entrypoint
RUN chmod +x /usr/local/bin/entrypoint

EXPOSE 8080

ENTRYPOINT ["/usr/local/bin/entrypoint"]
CMD ["supervisord", "-c", "/etc/supervisord.conf"]
