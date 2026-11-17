FROM php:8.2-fpm-alpine

# Cài đặt extension hệ thống và PHP cần thiết
RUN apk update && apk add --no-cache \
    git \
    curl \
    libpng-dev \
    libxml2-dev \
    zip \
    unzip \
    linux-headers \
    $PHPIZE_DEPS

# Cài đặt PHP extensions: pdo, pdo_mysql, pcntl, bcmath, gd
RUN docker-php-ext-install pdo pdo_mysql pcntl bcmath gd

# Cài đặt Redis extension
RUN pecl install redis && docker-php-ext-enable redis

# Cài đặt Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

COPY . .

EXPOSE 8000 8080

CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8000"]
