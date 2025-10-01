# Advanced Safety - Laravel Application

A Laravel application with Docker support, MySQL, and Redis.

## Quick Start

```bash
make install
```

Access the application at: **http://localhost:8000**

## Make Commands

```bash
make up         # Start containers
make down       # Stop and remove containers
make restart    # Restart containers
make install    # Full setup (first time)
make migrate    # Run migrations
make shell      # Access app container
```

## Database Connection

**MySQL:**
```bash
# Command Line
mysql -h 127.0.0.1 -P 3306 -u laravel -plaravel_password advanced_safety

# GUI Clients (TablePlus, MySQL Workbench, etc.)
Host:     127.0.0.1
Port:     3306
Username: laravel
Password: laravel_password
Database: advanced_safety
```

**Redis:**
```bash
redis-cli -h 127.0.0.1 -p 6379
```

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
