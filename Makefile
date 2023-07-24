PORT ?= 8000
start:
	PHP_CLI_SERVER_WORKERS=5 php -S 0.0.0.0:$(PORT) -t public
lint:
	composer exec --verbose phpcs -- --standard=PSR12 src public
phpstan:
	vendor/bin/phpstan analyse --level 8 src public
install:
	curl -sS https://getcomposer.org/installer -o composer-setup.php
	php composer-setup.php
	composer --version
	composer install
test:
	XDEBUG_MODE=coverage composer exec --verbose phpunit tests -- --coverage-text