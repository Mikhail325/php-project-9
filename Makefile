PORT ?= 8000
start1:
	PHP_CLI_SERVER_WORKERS=5 php -S 0.0.0.0:$(PORT)  -t public
lint:
	composer exec --verbose phpcs -- --standard=PSR12 app public
start:
  PHP_CLI_SERVER_WORKERS=5 php -S 0.0.0.0:$(PORT) -t public