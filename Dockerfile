FROM composer
WORKDIR /app

COPY . /app
RUN install-php-extensions \
    pdo \
    pdo_pgsql
RUN composer install
CMD ["make", "start"] 