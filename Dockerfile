FROM composer
WORKDIR /app

COPY . /app
RUN apt-get update && \
    apt-get install -y libpq-dev && \
    docker-php-ext-install pdo pdo_pgsql pgsql
RUN composer install
CMD ["make", "start"] 