#!/bin/bash
set -e

cd /var/www/html

# ── 1. Render fournit la variable PORT : faire écouter Apache dessus ──
PORT="${PORT:-10000}"
sed -ri "s/^Listen 80$/Listen ${PORT}/" /etc/apache2/ports.conf
sed -ri "s/:80>/:${PORT}>/" /etc/apache2/sites-available/000-default.conf

# ── 2. Lien public/storage (fichiers uploadés : cahier, bibliothèque, photos) ──
php artisan storage:link 2>/dev/null || true

# ── 3. Caches de production (nécessitent APP_KEY et les variables d'env) ──
php artisan config:cache  || true
php artisan route:cache   || true
php artisan view:cache    || true

# ── 4. Migrations automatiques si demandé (RUN_MIGRATIONS=true) ──
if [ "${RUN_MIGRATIONS}" = "true" ]; then
    php artisan migrate --force || true
fi

# ── 5. Lancer Apache au premier plan ──
exec apache2-foreground
