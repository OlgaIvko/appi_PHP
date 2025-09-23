# # FROM php:8.2-fpm

# # # Установка системных зависимостей
# # RUN apt-get update && apt-get install -y \
# #     git \
# #     curl \
# #     libpng-dev \
# #     libonig-dev \
# #     libxml2-dev \
# #     libzip-dev \
# #     zip \
# #     unzip \
# #     default-mysql-client \
# #     && apt-get clean \
# #     && rm -rf /var/lib/apt/lists/*

# # # Установка PHP расширений
# # RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd zip

# # # Установка Composer
# # COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# # # Создание рабочей директории
# # WORKDIR /var/www/html

# # # Копирование файлов зависимостей
# # # COPY src/composer.json src/composer.lock ./
# # COPY composer.json composer.lock ./

# # # Установка зависимостей (кэширование)
# # RUN composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader

# # # Копирование исходного кода
# # COPY src/ .

# # # Настройка прав
# # RUN chown -R www-data:www-data /var/www/html \
# #     && chmod -R 755 /var/www/html/storage \
# #     && chmod -R 755 /var/www/html/bootstrap/cache

# # # Создание пользователя
# # RUN useradd -G www-data,root -u 1000 -d /home/devuser devuser \
# #     && mkdir -p /home/devuser/.composer \
# #     && chown -R devuser:devuser /home/devuser

# # # Переключение на пользователя
# # USER devuser

# # # Открытие порта
# # EXPOSE 9000

# # # Команда по умолчанию
# # CMD ["php-fpm"]


# FROM php:8.2-fpm
# RUN usermod -u 1000 www-data && groupmod -g 1000 www-data

# # Устанавливаем cron
# RUN apt-get update && apt-get install -y cron
# # Установка системных зависимостей
# RUN apt-get update && apt-get install -y \
#     git \
#     curl \
#     libpng-dev \
#     libonig-dev \
#     libxml2-dev \
#     libzip-dev \
#     zip \
#     unzip \
#     default-mysql-client \
#     cron \
#     && apt-get clean \
#     && rm -rf /var/lib/apt/lists/*

# # Установка PHP расширений
# RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd zip

# # Установка Composer
# COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# # Создание рабочей директории
# WORKDIR /var/www/html

# # Копирование файлов зависимостей
# COPY composer.json composer.lock ./

# # Установка зависимостей (кэширование)
# RUN composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader

# # Копирование исходного кода
# COPY src/ .

# # Настройка прав
# RUN chown -R www-data:www-data /var/www/html \
#     && chmod -R 755 /var/www/html/storage \
#     && chmod -R 755 /var/www/html/bootstrap/cache

# # Создание пользователя
# RUN useradd -G www-data,root -u 1000 -d /home/devuser devuser \
#     && mkdir -p /home/devuser/.composer \
#     && chown -R devuser:devuser /home/devuser

# # Переключение на пользователя
# USER devuser

# # Открытие порта
# EXPOSE 9000

# # Команда по умолчанию
# CMD ["php-fpm"]




FROM php:8.2-fpm

# Устанавливаем пользователя www-data с правильными UID/GID
RUN usermod -u 1000 www-data && groupmod -g 1000 www-data

# Устанавливаем cron и системные зависимости
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libzip-dev \
    zip \
    unzip \
    default-mysql-client \
    cron \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Установка PHP расширений
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd zip

# Включаем расширение session
RUN docker-php-ext-install session

# Установка Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Создание рабочей директории
WORKDIR /var/www/html

# Копирование файлов зависимостей
COPY composer.json composer.lock ./

# Установка зависимостей (кэширование)
RUN composer install --no-interaction --prefer-dist --optimize-autoloader

# Копирование исходного кода
COPY . .

# Настройка прав
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html/storage \
    && chmod -R 755 /var/www/html/bootstrap/cache

# Настройка cron
RUN echo '0 9,21 * * * /scripts/update_data.sh' > /etc/cron.d/data-update \
    && chmod 0644 /etc/cron.d/data-update \
    && crontab /etc/cron.d/data-update

# Создание пользователя
RUN useradd -G www-data,root -u 1000 -d /home/devuser devuser \
    && mkdir -p /home/devuser/.composer \
    && chown -R devuser:devuser /home/devuser

# Переключение на пользователя
USER devuser

# Открытие порта
EXPOSE 9000

# Команда по умолчанию (запускаем cron и php-fpm)
CMD sh -c "cron && php-fpm"


# # Команда по умолчанию


