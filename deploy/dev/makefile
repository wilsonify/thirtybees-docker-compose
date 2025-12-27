
all: php down up
php:
	docker build --progress=plain --platform=amd64 --tag "ghcr.io/wilsonify/thirtybees:1.0.0" -f Dockerfile .

down:
	docker-compose down

up: down
	docker-compose up -d
