FROM composer
WORKDIR /app

COPY . /app
RUN apt-get install php-pgsql
RUN composer install
CMD ["make", "start"] 