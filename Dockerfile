FROM php:8.2-cli

WORKDIR /app

COPY . /app

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
RUN sudo apt install zip unzip php-zip
ENV COMPOSER_ALLOW_SUPERUSER=1
RUN composer update --lock
RUN composer install
CMD ["make", "start"] 