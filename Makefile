PORT ?= 80
start:
	PHP_CLI_SERVER_WORKERS=5 php -S 0.0.0.0:$(PORT) -t public
lint:
	composer exec --verbose phpcs -- --standard=PSR12 app public
install:
	composer install
test:
	XDEBUG_MODE=coverage composer exec --verbose phpunit tests -- --coverage-text
test-html:
	XDEBUG_MODE=coverage composer exec --verbose phpunit tests -- --coverage-html tests/coverage
test-xml:
	XDEBUG_MODE=coverage composer exec --verbose phpunit tests -- --coverage-clover tests/coverage.xml