# Docker Container Management
up:
	docker-compose up -d

down:
	docker-compose down

restart:
	docker-compose restart

# Database Connections
# MySQL: mysql -h 127.0.0.1 -P 3306 -u laravel -plaravel_password advanced_safety
# Redis: redis-cli -h 127.0.0.1 -p 6379
