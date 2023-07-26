FROM composer
WORKDIR /app

COPY . /app
RUN composer install
CMD ["make", "start"] 