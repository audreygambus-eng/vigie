FROM php:8.3-cli

RUN apt-get update && apt-get install -y git unzip libssl-dev \
    && pecl install mongodb-1.21.5 \
    && docker-php-ext-enable mongodb \
    && docker-php-ext-install pdo_mysql

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
WORKDIR /app