.PHONY: help build up down restart stop start logs shell install migrate fresh seed clean rebuild status

# Default target
help:
	@echo "Available commands:"
	@echo "  make build       - Build Docker containers"
	@echo "  make up          - Start and create containers"
	@echo "  make down        - Stop and remove containers"
	@echo "  make restart     - Restart all containers"
	@echo "  make stop        - Stop containers without removing"
	@echo "  make start       - Start existing containers"
	@echo "  make logs        - View container logs"
	@echo "  make shell       - Access app container shell"
	@echo "  make install     - Install dependencies and setup app"
	@echo "  make migrate     - Run database migrations"
	@echo "  make fresh       - Fresh migration with seed"
	@echo "  make seed        - Seed the database"
	@echo "  make clean       - Remove containers and volumes"
	@echo "  make rebuild     - Rebuild containers from scratch"
	@echo "  make status      - Show container status"

# Build containers
build:
	docker-compose build

# Start containers
up:
	docker-compose up -d
	@echo "Containers started! Access the app at http://localhost:8000"

# Stop and remove containers
down:
	docker-compose down

# Restart containers
restart:
	docker-compose restart
	@echo "Containers restarted!"

# Stop containers
stop:
	docker-compose stop

# Start stopped containers
start:
	docker-compose start

# View logs
logs:
	docker-compose logs -f

# Access app container shell
shell:
	docker-compose exec app bash

# Install dependencies and setup application
install: up
	@echo "Installing Composer dependencies..."
	docker-compose exec app composer install
	@echo "Installing NPM dependencies..."
	docker-compose exec app npm install
	@echo "Building assets..."
	docker-compose exec app npm run build
	@echo "Generating application key..."
	docker-compose exec app php artisan key:generate
	@echo "Running migrations..."
	docker-compose exec app php artisan migrate
	@echo "Installation complete!"

# Run database migrations
migrate:
	docker-compose exec app php artisan migrate

# Fresh migration with seed
fresh:
	docker-compose exec app php artisan migrate:fresh --seed

# Seed database
seed:
	docker-compose exec app php artisan db:seed

# Remove containers and volumes
clean:
	docker-compose down -v
	@echo "Containers and volumes removed!"

# Rebuild containers from scratch
rebuild: clean
	docker-compose build --no-cache
	docker-compose up -d
	@echo "Containers rebuilt!"

# Show container status
status:
	docker-compose ps
