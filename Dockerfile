# Используем PHP-образ с поддержкой нужных расширений
FROM php:8.2-fpm

# Устанавливаем необходимые пакеты и расширения
RUN apt-get update && apt-get install -y \
    libpq-dev \
    libonig-dev \
    libzip-dev \
    zip \
    unzip \
    git \
    curl \
    && docker-php-ext-install pdo pdo_mysql mbstring zip

# Установка Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Создаем директорию для приложения
WORKDIR /var/www

# Копируем файлы Composer (composer.json и composer.lock)
COPY src/composer.json src/composer.lock ./

# Очищаем кеш Composer и устанавливаем зависимости
RUN composer clear-cache \
    && composer install --no-dev --optimize-autoloader --ignore-platform-reqs --verbose --no-scripts --no-plugins

# Копируем остальные файлы приложения
COPY src/ ./

# Устанавливаем права на директории хранения и кеша
RUN chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache

# Устанавливаем рабочий пользователь для выполнения приложения
USER www-data

# Порт, который будет использоваться
EXPOSE 9000

# Запуск php-fpm
CMD ["php-fpm"]
