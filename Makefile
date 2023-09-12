PORT ?= 8000
start:
	PHP_CLI_SERVER_WORKERS=5 php -S 0.0.0.0:$(PORT) -t public
lint:
	composer exec --verbose phpcs -- --standard=PSR12 src public
phpstan:
	vendor/bin/phpstan analyse --level 8 src public
install:
	composer install
build:
	docker build -t user_name/page-analis .
run:	
	docker run -p 8000:8000 user_name/page-analis