FROM composer:2 AS composer_bin

FROM php:8.3-fpm-alpine AS build

RUN apk add --no-cache \
    git \
    unzip \
    curl \
    icu-dev \
    libzip-dev \
    oniguruma-dev \
    libpng-dev \
    libjpeg-turbo-dev \
    freetype-dev \
    nodejs \
    npm \
    bash \
    $PHPIZE_DEPS \
  && docker-php-ext-configure gd --with-freetype --with-jpeg \
  && docker-php-ext-install -j"$(nproc)" pdo_mysql mbstring bcmath intl zip gd opcache \
  && pecl install redis \
  && docker-php-ext-enable redis

COPY --from=composer_bin /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

COPY composer.json composer.lock ./
RUN composer install \
    --no-dev \
    --prefer-dist \
    --no-interaction \
    --no-progress \
    --no-scripts \
    --optimize-autoloader

COPY package.json package-lock.json ./
RUN npm ci

COPY . .

RUN npm run build \
 && composer dump-autoload --optimize --classmap-authoritative

FROM php:8.3-fpm-alpine AS runtime

RUN apk add --no-cache \
    icu \
    libzip \
    oniguruma \
    libpng \
    libjpeg-turbo \
    freetype \
    bash \
  mariadb-client \
    curl \
  && apk add --no-cache --virtual .build-deps \
    icu-dev \
    libzip-dev \
    oniguruma-dev \
    libpng-dev \
    libjpeg-turbo-dev \
    freetype-dev \
    $PHPIZE_DEPS \
  && docker-php-ext-configure gd --with-freetype --with-jpeg \
  && docker-php-ext-install -j"$(nproc)" pdo_mysql mbstring bcmath intl zip gd opcache \
  && pecl install redis \
  && docker-php-ext-enable redis \
  && apk del .build-deps

COPY --from=composer_bin /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

COPY --from=build --chown=www-data:www-data /var/www/html /var/www/html
COPY --chown=www-data:www-data docker/entrypoint.sh /usr/local/bin/entrypoint.sh

RUN chmod +x /usr/local/bin/entrypoint.sh \
 && mkdir -p storage/framework/{cache,sessions,views} storage/logs bootstrap/cache \
 && chown -R www-data:www-data storage bootstrap/cache \
 && chmod -R ug+rwX storage bootstrap/cache

USER www-data

EXPOSE 9000

ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
CMD ["php-fpm"]
