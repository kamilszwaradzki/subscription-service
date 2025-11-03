DC ?= docker-compose

PHP_SERVICE ?= app

build-dev:
	$(DC) -f docker-compose.yml -f docker-compose.dev.yml build

start-dev:
	$(DC) -f docker-compose.yml -f docker-compose.dev.yml up -d

stop-dev:
	$(DC) -f docker-compose.yml -f docker-compose.dev.yml down

restart-dev: stop-dev start-dev

build-prod:
	$(DC) -f docker-compose.yml -f docker-compose.prod.yml build

start-prod:
	$(DC) -f docker-compose.yml -f docker-compose.prod.yml up -d

stop-prod:
	$(DC) -f docker-compose.yml -f docker-compose.prod.yml down

restart-prod: stop-prod start-prod

logs:
	$(DC) logs

test:
	$(DC) exec ${PHP_SERVICE} vendor/bin/phpunit --testdox

shell:
	$(DC) exec ${PHP_SERVICE} sh

health:
	@echo ""
	@echo "🏥 Health check:"
	@curl -s http://localhost:8080/health
	@echo ""