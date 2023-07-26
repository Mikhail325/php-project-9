FROM php:8.2-cli

WORKDIR /app

COPY . /app

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
RUN ls -al
RUN composer install
CMD ["make", "start"] 