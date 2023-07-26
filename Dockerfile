FROM composer
WORKDIR /app

COPY . /app
RUN docker-php-ext-install pdo pdo_mysql pdo_pgsql
RUN composer install
CMD ["make", "start"] 