#!/bin/sh
# Runs on every machine boot, before supervisord takes over.
#
# The Fly volume is mounted at /data and is the only thing that survives a
# deploy: the SQLite file, every tenant's uploads, and the framework caches
# all live there. /var/www/html/storage is a symlink into it.
set -e

DATA=/data

echo "==> preparing volume at $DATA"

# ---- storage tree on the volume -------------------------------------------
# Laravel needs these before it can boot at all; a missing framework/cache is
# what turns the first real-time facade write into a 500 on livewire/update.
mkdir -p \
    "$DATA/storage/app/public" \
    "$DATA/storage/app/private" \
    "$DATA/storage/app/livewire-tmp" \
    "$DATA/storage/framework/cache/data" \
    "$DATA/storage/framework/sessions" \
    "$DATA/storage/framework/views" \
    "$DATA/storage/logs" \
    "$DATA/storage/backups"

# Point the app at the volume. The image ships a storage/ directory; replace
# it wholesale, then put the shipped fonts back (see below).
rm -rf /var/www/html/storage
ln -sfn "$DATA/storage" /var/www/html/storage

# ---- Bengali fonts ---------------------------------------------------------
# PdfFactory reads base_path('storage/fonts'), which now resolves into the
# volume. The fonts are shipped code, not user data, so copy the image's copy
# over the volume's on every boot — a font added in a later deploy lands here.
mkdir -p "$DATA/storage/fonts"
cp -f /usr/local/share/qawmi-fonts/*.ttf "$DATA/storage/fonts/" 2>/dev/null || true
cp -f /usr/local/share/qawmi-fonts/*.txt "$DATA/storage/fonts/" 2>/dev/null || true
# mPDF writes parsed font metrics next to the fonts; it must be writable.
mkdir -p "$DATA/storage/fonts/ttfontdata" "$DATA/storage/app/mpdf"

# ---- database --------------------------------------------------------------
# Render (and Heroku, and most managed Postgres) publishes one DATABASE_URL.
# Laravel only ever looks at DB_URL, so without this bridge DB_HOST keeps its
# config/database.php default of 127.0.0.1 and migrations die with
# "connection to server at 127.0.0.1 port 5432 failed: Connection refused" —
# the container dialling itself. Fly sets no DATABASE_URL, so this is a no-op
# there. An explicit DB_URL always wins.
if [ -n "$DATABASE_URL" ] && [ -z "$DB_URL" ]; then
    echo "==> using DATABASE_URL for the database connection"
    export DB_URL="$DATABASE_URL"
    export DB_CONNECTION="${DB_CONNECTION:-pgsql}"
fi

# ---- SQLite ----------------------------------------------------------------
# Only when actually running SQLite: under pgsql, DB_DATABASE is a database
# *name* ("qawmi"), and touching it would litter the app root with a stray
# file instead of creating anything useful.
if [ "${DB_CONNECTION:-sqlite}" = "sqlite" ] && [ -z "$DB_URL" ]; then
    DB_FILE="${DB_DATABASE:-$DATA/database.sqlite}"
    if [ ! -f "$DB_FILE" ]; then
        echo "==> creating fresh database at $DB_FILE"
        touch "$DB_FILE"
    fi
fi

chown -R nginx:nginx "$DATA" /var/www/html/bootstrap/cache
chmod -R u+rwX "$DATA"

# ---- application boot ------------------------------------------------------
cd /var/www/html

# Never migrate:fresh here — the volume holds real madrasa data.
echo "==> running migrations"
php artisan migrate --force --no-interaction

# Reconcile PermissionRegistry against the DB; new permissions ship in code.
php artisan permissions:sync --no-interaction || true

# public/storage + public/storage/tenants/{id} symlinks, recreated because
# public/ is rebuilt on every deploy while the volume keeps the files.
php artisan storage:link --force --no-interaction || true
php artisan storage:link-tenants --force --no-interaction || true

# Cache config/routes/views. Do this last: an earlier failure should surface
# with a readable error, not a cached broken config.
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo "==> boot complete"
exec "$@"
