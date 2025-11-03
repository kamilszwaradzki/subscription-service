# Subscription Service (in progress)
### System zarządzania cyklicznymi subskrypcjami z webhookami płatności i retry logic.

Minimalna usługa subskrypcji w Symfony.  
Cel: pokazać architekturę backendową, event flow i testy.

## Status
✅ Healthcheck endpoint  
🔧 W toku: model subskrypcji, migracje, testy, CI

## Run
### development
docker-compose -f docker-compose.yml -f docker-compose.dev.yml up -d\
make migrate-dev

### production
docker-compose -f docker-compose.yml -f docker-compose.prod.yml up -d\
make migrate-prod

make test
