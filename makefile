
php:
	docker build --progress=plain --platform=amd64 --tag "ghcr.io/wilsonify/thirtybees:1.0.0" -f Dockerfile .

up:
	docker-compose up -d

down:
	docker-compose down