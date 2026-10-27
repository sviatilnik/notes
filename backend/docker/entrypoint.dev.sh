#!/bin/sh
set -e

mkdir -p /var/www/html/var/data /var/www/html/var/cache /var/www/html/var/log
chmod +x /var/www/html/bin/console 2>/dev/null || true

composer install --prefer-dist --no-progress

chown -R www-data:www-data /var/www/html/var
chmod -R ug+rwX /var/www/html/var

su-exec www-data php bin/console doctrine:migrations:migrate --no-interaction --allow-no-migration --env=dev

exec su-exec www-data php -S 0.0.0.0:8000 -t public public/index.php
