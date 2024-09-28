FROM php:8.2.1-fpm

# Установка рабочей директории
WORKDIR /var/www/html

# Обновление и установка необходимых расширений
RUN apt-get -y update && apt-get upgrade -y
RUN docker-php-ext-install pdo pdo_mysql && docker-php-ext-enable pdo_mysql

# Установка Xdebug и подключение его (docker-php-ext-enable xdebug)
RUN pecl install xdebug-3.2.0 && docker-php-ext-enable xdebug

# для Xdebug нужен конфиг (будем копировать в папку где есть уже дефолтные конфиги кот исп-ся php)
COPY docker/php/conf.d/* $PHP_INI_DIR/conf.d/


# Установка Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer


CMD ["php-fpm"]