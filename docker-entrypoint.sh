#!/bin/sh
set -e
# Keep .env (with its unique secret key) on the data volume so it survives image upgrades.
if [ ! -f /data/.env ]; then
    sed -e "s#^APP_KEY=.*#APP_KEY=base64:$(head -c 32 /dev/urandom | base64)#" \
        -e "s#^APP_ENV=.*#APP_ENV=production#" -e "s#^APP_DEBUG=.*#APP_DEBUG=false#" \
        .env.example > /data/.env
fi
ln -sf /data/.env .env
touch /data/database.sqlite
chown -R www-data:www-data /data storage bootstrap/cache
su -s /bin/sh www-data -c "php artisan migrate --force"
exec "$@"
