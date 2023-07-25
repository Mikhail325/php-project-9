FROM php:8.2-cli

WORKDIR /app

COPY . /app

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
RUN composer --no-interaction install --no-plugins --no-scripts
CMD ["make", "start"] 