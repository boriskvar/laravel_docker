FROM php:8.2.1-fpm

# Установка рабочей директории
WORKDIR /var/www/html

# Обновление, установка PHP-расширений и Node.js
RUN apt-get -y update && apt-get upgrade -y \
    && apt-get install -y \
       curl \
       gnupg \
       unzip \
    && docker-php-ext-install pdo pdo_mysql \
    && docker-php-ext-enable pdo_mysql \
    && curl -fsSL https://deb.nodesource.com/setup_18.x | bash - \
    && apt-get install -y nodejs \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Установка Composer
COPY --from=composer:latest /usr/bin/composer /usr/local/bin/composer
RUN chmod +x /usr/local/bin/composer

# Копирование всех остальных файлов проекта
COPY src/ ./

# Копирование файлов composer
COPY src/composer.json src/composer.lock ./

# Установка зависимостей через Composer
RUN composer install --no-dev --optimize-autoloader

# Копирование конфигурационных файлов
COPY docker/nginx/conf.d/ /etc/nginx/conf.d/

# Указание пути для Xdebug
RUN echo "xdebug.remote_host=host.docker.internal" >> $PHP_INI_DIR/conf.d/xdebug.ini

CMD ["php-fpm"]
