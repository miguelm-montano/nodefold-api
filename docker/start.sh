#!/bin/bash

cat > /var/www/.env << EOF
APP_NAME=Nodefold
APP_ENV=${APP_ENV:-production}
APP_KEY=${APP_KEY}
APP_DEBUG=${APP_DEBUG:-false}
APP_URL=${APP_URL}

LOG_CHANNEL=stderr
LOG_LEVEL=error

DB_CONNECTION=pgsql
DB_URL=${DATABASE_URL}

BROADCAST_DRIVER=log
CACHE_DRIVER=file
QUEUE_CONNECTION=sync
SESSION_DRIVER=file
EOF

php artisan migrate --force
php artisan passport:install --force
php artisan db:seed --class=AdminSeeder --force
chown -R www-data:www-data /var/www
su -s /bin/bash www-data -c "php /var/www/artisan scribe:generate"
php-fpm -D
sleep 2
nginx -g "daemon off;"