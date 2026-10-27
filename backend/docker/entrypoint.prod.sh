#!/bin/sh
set -e

mkdir -p /var/www/html/var/data /var/www/html/var/cache/prod /var/www/html/var/log
chown -R www-data:www-data /var/www/html/var
chmod -R ug+rwX /var/www/html/var

su-exec www-data php bin/console doctrine:migrations:migrate --no-interaction --allow-no-migration --env=prod

exec php-fpm
