FROM php:8.4-fpm-alpine AS php_base

RUN apk add --no-cache icu-dev libzip-dev sqlite-dev icu-libs libzip sqlite-libs su-exec git $PHPIZE_DEPS \
    && docker-php-ext-install -j"$(nproc)" intl zip pdo_sqlite opcache

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

ENV COMPOSER_ALLOW_SUPERUSER=1 \
    COMPOSER_NO_INTERACTION=1

FROM php_base AS composer_prod

ENV APP_ENV=prod \
    APP_DEBUG=0

COPY composer.json composer.lock ./
COPY bin bin
COPY config config
COPY migrations migrations
COPY public public
COPY src src
COPY templates templates
COPY .env ./

RUN composer install \
        --no-dev \
        --prefer-dist \
        --no-progress \
        --no-scripts \
        --optimize-autoloader \
    && rm -rf /root/.composer /tmp/*

FROM php:8.4-fpm-alpine AS production

RUN apk add --no-cache icu-libs libzip sqlite-libs su-exec

COPY --from=php_base /usr/local/lib/php/extensions/ /usr/local/lib/php/extensions/
COPY --from=php_base /usr/local/etc/php/conf.d/ /usr/local/etc/php/conf.d/
COPY docker/php.ini /usr/local/etc/php/conf.d/notes.ini

WORKDIR /var/www/html

ENV APP_ENV=prod \
    APP_DEBUG=0

COPY --from=composer_prod --chown=www-data:www-data /var/www/html /var/www/html
COPY docker/entrypoint.prod.sh /usr/local/bin/entrypoint.sh

RUN chmod +x /usr/local/bin/entrypoint.sh bin/console \
    && mkdir -p var/data var/cache var/log \
    && chown -R www-data:www-data var

EXPOSE 9000

ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]

FROM php_base AS development

ENV APP_ENV=dev \
    APP_DEBUG=1

COPY docker/php.dev.ini /usr/local/etc/php/conf.d/notes.ini
COPY docker/entrypoint.dev.sh /usr/local/bin/entrypoint.sh

RUN chmod +x /usr/local/bin/entrypoint.sh \
    && mkdir -p /var/www/html/var \
    && chown -R www-data:www-data /var/www/html

EXPOSE 8000

ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
