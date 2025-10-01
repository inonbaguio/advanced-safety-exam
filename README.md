# Advanced Safety - Laravel Application

A Laravel application with Docker support, MySQL, and Redis.

## Prerequisites

- Docker and Docker Compose installed on your system
- Git (optional, for cloning the repository)

## Getting Started with Docker

### Quick Start with Makefile (Recommended)

```bash
# Build and start containers with full setup
make install
```

This single command will:
- Build and start all containers (MySQL, Redis, PHP-FPM, Nginx)
- Install Composer dependencies
- Install NPM dependencies
- Build frontend assets
- Generate application key
- Run database migrations

Access the application at: **http://localhost:8000**

### Available Make Commands

```bash
make help       # Show all available commands
make up         # Start containers
make down       # Stop and remove containers
make restart    # Restart containers
make stop       # Stop containers
make start      # Start stopped containers
make logs       # View container logs
make shell      # Access app container shell
make migrate    # Run migrations
make fresh      # Fresh migration with seed
make clean      # Remove containers and volumes
make rebuild    # Rebuild from scratch
make status     # Show container status
```

### Manual Setup (Without Makefile)

If you prefer to run commands manually:

#### 1. Build and Start Containers

```bash
docker-compose up -d --build
```

#### 2. Install Dependencies

```bash
docker-compose exec app composer install
docker-compose exec app npm install
docker-compose exec app npm run build
```

#### 3. Setup Application

```bash
docker-compose exec app php artisan key:generate
docker-compose exec app php artisan migrate
```

#### 4. Access the Application

Open your browser and navigate to:
```
http://localhost:8000
```

## Docker Commands

### Start the containers
```bash
docker-compose up -d
```

### Stop the containers
```bash
docker-compose down
```

### View logs
```bash
# All services
docker-compose logs -f

# Specific service
docker-compose logs -f app
docker-compose logs -f mysql
docker-compose logs -f redis
```

### Access container shell
```bash
# Application container
docker-compose exec app bash

# MySQL container
docker-compose exec mysql bash

# Redis CLI
docker-compose exec redis redis-cli
```

### Run Laravel commands
```bash
# Artisan commands
docker-compose exec app php artisan migrate
docker-compose exec app php artisan cache:clear
docker-compose exec app php artisan config:clear

# Composer commands
docker-compose exec app composer install
docker-compose exec app composer update

# NPM commands
docker-compose exec app npm install
docker-compose exec app npm run dev
```

## Connecting to the Database

### Database Credentials

The Docker setup provides two user accounts:

**Standard User (Recommended):**
- **Host**: `localhost` or `127.0.0.1`
- **Port**: `3306`
- **Database**: `advanced_safety`
- **Username**: `laravel`
- **Password**: `laravel_password`

**Root User:**
- **Username**: `root`
- **Password**: `root_password`

### Connection Methods

#### 1. MySQL Command Line (From Host Machine)

```bash
# Connect as laravel user
mysql -h 127.0.0.1 -P 3306 -u laravel -plaravel_password advanced_safety

# Connect as root user
mysql -h 127.0.0.1 -P 3306 -u root -proot_password advanced_safety
```

#### 2. From Docker Container

```bash
# Connect from MySQL container
docker-compose exec mysql mysql -u laravel -plaravel_password advanced_safety

# Connect from app container (requires mysql client installed)
docker-compose exec app mysql -h mysql -u laravel -plaravel_password advanced_safety
```

#### 3. GUI Database Clients

Configure your preferred GUI client (TablePlus, MySQL Workbench, DBeaver, phpMyAdmin, etc.):

**Connection Settings:**
```
Host:     localhost (or 127.0.0.1)
Port:     3306
Username: laravel
Password: laravel_password
Database: advanced_safety
```

**Popular GUI Clients:**
- [TablePlus](https://tableplus.com/) - Modern, native client for Mac, Windows, Linux
- [MySQL Workbench](https://www.mysql.com/products/workbench/) - Official MySQL GUI
- [DBeaver](https://dbeaver.io/) - Universal database tool
- [phpMyAdmin](https://www.phpmyadmin.net/) - Web-based MySQL administration

#### 4. From Laravel Application

The application automatically connects using the environment variables defined in `docker-compose.yml`:

```env
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=advanced_safety
DB_USERNAME=laravel
DB_PASSWORD=laravel_password
```

### Useful Database Commands

```bash
# Show all databases
docker-compose exec mysql mysql -u laravel -plaravel_password -e "SHOW DATABASES;"

# Show all tables in advanced_safety database
docker-compose exec mysql mysql -u laravel -plaravel_password advanced_safety -e "SHOW TABLES;"

# Show current connections
docker-compose exec mysql mysql -u laravel -plaravel_password -e "SHOW PROCESSLIST;"

# Export database
docker-compose exec mysql mysqldump -u laravel -plaravel_password advanced_safety > backup.sql

# Import database
docker-compose exec -T mysql mysql -u laravel -plaravel_password advanced_safety < backup.sql
```

## Redis Configuration

### Redis Connection Details

**From Host Machine:**
- **Host**: `localhost` or `127.0.0.1`
- **Port**: `6379`
- **Password**: None (no password set)

**From Docker Network:**
- **Host**: `redis`
- **Port**: `6379`

### Connecting to Redis

```bash
# Redis CLI from host machine
redis-cli -h 127.0.0.1 -p 6379

# Redis CLI from Docker container
docker-compose exec redis redis-cli

# Test Redis connection
docker-compose exec redis redis-cli ping
# Should return: PONG
```

### Useful Redis Commands

```bash
# View all keys
docker-compose exec redis redis-cli KEYS '*'

# Flush all data (clear cache)
docker-compose exec redis redis-cli FLUSHALL

# Get Redis info
docker-compose exec redis redis-cli INFO

# Monitor Redis commands in real-time
docker-compose exec redis redis-cli MONITOR
```

## Troubleshooting

### Permission Issues
If you encounter permission errors with storage or cache:
```bash
docker-compose exec app chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
docker-compose exec app chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache
```

### Clear All Caches
```bash
docker-compose exec app php artisan cache:clear
docker-compose exec app php artisan config:clear
docker-compose exec app php artisan route:clear
docker-compose exec app php artisan view:clear
```

### Rebuild Containers
```bash
docker-compose down
docker-compose up -d --build
```

### Reset Database
```bash
docker-compose exec app php artisan migrate:fresh --seed
```

## Environment Variables

The application uses environment variables defined in `.env` file. When using Docker, the following variables are automatically configured in `docker-compose.yml`:

- `DB_HOST=mysql`
- `DB_DATABASE=advanced_safety`
- `DB_USERNAME=laravel`
- `DB_PASSWORD=laravel_password`
- `REDIS_HOST=redis`
- `CACHE_STORE=redis`
- `SESSION_DRIVER=redis`

## Development

For local development without Docker, see the [Laravel documentation](https://laravel.com/docs).

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
