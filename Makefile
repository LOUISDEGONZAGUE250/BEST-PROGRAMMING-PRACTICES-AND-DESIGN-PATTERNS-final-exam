# Convenience Makefile for common tasks
.PHONY: build up init logs down

build:
	docker-compose build --no-cache

up:
	docker-compose up -d --build

init:
	docker-compose run --rm init

logs:
	docker-compose logs -f

down:
	docker-compose down -v
