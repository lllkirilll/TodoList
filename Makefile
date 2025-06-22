# Makefile for Docker and Symfony project management

.PHONY: up down build restart ssh composer-install composer-update \
        make-entity make-migration migrate seed jwt-keys

# Start containers in detached mode and install composer dependencies
up:
	docker compose up -d
	@echo "Waiting for containers to start..."
	sleep 5
	$(MAKE) composer-install

# Stop and remove containers
down:
	docker compose down

# Rebuild and start containers
build:
	docker compose up -d --build

# Restart containers
restart:
	docker compose restart

# Connect to PHP container via bash
ssh:
	docker compose exec php bash

# Install composer dependencies
composer-install:
	docker compose exec php composer install

# Update composer dependencies
composer-update:
	docker compose exec php composer update

# Create new entity
make-entity:
	docker compose exec php php bin/console make:entity

# Generate migration based on entity changes
make-migration:
	docker compose exec php php bin/console make:migration

# Apply migrations to database
migrate:
	docker compose exec php php bin/console doctrine:migrations:migrate

seed:
	docker compose exec php php bin/console doctrine:fixtures:load --no-interaction

jwt-keys:
	@echo "Generating JWT keys..."
	docker compose exec php mkdir -p config/jwt
	docker compose exec php openssl genpkey -out config/jwt/private.pem -aes256 -algorithm rsa -pkeyopt rsa_keygen_bits:4096
	docker compose exec php openssl pkey -in config/jwt/private.pem -out config/jwt/public.pem -pubout
	docker compose exec php chown -R www-data:www-data config/jwt
	@echo "✅ JWT keys generated successfully!"
	@echo "IMPORTANT: Please update your JWT_PASSPHRASE in symfony/.env.local with the passphrase you just set."