---
name: cicd_containerization
description: Guide for Docker, Docker Compose, CI/CD pipelines (GitHub Actions), and automation.
---

# CI/CD & Containerization Assistant

Guide for modernizing deployment with Containers and Automation.

## 📑 Table of Contents

- [Docker Setup](#-docker-setup)
- [Docker Compose](#-docker-compose)
- [CI/CD Pipelines](#-cicd-pipelines)
- [Secrets Management](#-secrets-management)
- [Deployment Automation](#-deployment-automation)

## 🐳 Docker Setup

### Dockerfile (PHP-FPM)

#### Single-Stage (Development)

Create `Dockerfile` in root:

```dockerfile
FROM php:8.3-fpm

# Install dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip

# Install extensions
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd

# Get Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www

# Copy existing application directory contents
COPY . /var/www

# Change current user to www
USER www-data

# Expose port 9000
EXPOSE 9000

CMD ["php-fpm"]
```

#### Multi-Stage (Production)

For smaller production images:

```dockerfile
# Build stage
FROM php:8.3-fpm AS builder

RUN apt-get update && apt-get install -y \
    git curl libpng-dev libonig-dev libxml2-dev zip unzip

RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www
COPY . /var/www

# Install PHP dependencies (no dev)
RUN composer install --no-dev --optimize-autoloader --no-interaction

# Production stage
FROM php:8.3-fpm

RUN apt-get update && apt-get install -y \
    libpng16-16 libonig5 libxml2 \
    && rm -rf /var/lib/apt/lists/*

RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd

WORKDIR /var/www

# Copy only necessary files from builder
COPY --from=builder /var/www /var/www

# Security: Remove .env.example, .git, tests
RUN rm -rf .git tests .env.example

USER www-data
EXPOSE 9000

CMD ["php-fpm"]
```

### Nginx Container

Create `docker/nginx/conf.d/app.conf`:

```nginx
server {
    listen 80;
    index index.php index.html;
    error_log  /var/log/nginx/error.log;
    access_log /var/log/nginx/access.log;
    root /var/www/public;

    location ~ \.php$ {
        try_files $uri =404;
        fastcgi_split_path_info ^(.+\.php)(/.+)$;
        fastcgi_pass app:9000;
        fastcgi_index index.php;
        include fastcgi_params;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        fastcgi_param PATH_INFO $fastcgi_path_info;
    }

    location / {
        try_files $uri $uri/ /index.php?$query_string;
        gzip_static on;
    }
}
```

## 🐙 Docker Compose

Create `docker-compose.yml`:

```yaml
version: '3.8'

services:
  app:
    build:
      context: .
      dockerfile: Dockerfile
    image: hr-budget-app
    container_name: hr-budget-app
    restart: unless-stopped
    working_dir: /var/www
    volumes:
      - ./:/var/www
    networks:
      - hr-budget-network
    healthcheck:
      test: ["CMD", "php-fpm-healthcheck"]
      interval: 30s
      timeout: 10s
      retries: 3
      start_period: 40s

  webserver:
    image: nginx:alpine
    container_name: hr-budget-nginx
    restart: unless-stopped
    ports:
      - "8080:80"
    volumes:
      - ./:/var/www
      - ./docker/nginx/conf.d/:/etc/nginx/conf.d/
    networks:
      - hr-budget-network
    depends_on:
      app:
        condition: service_healthy
    healthcheck:
      test: ["CMD", "wget", "--quiet", "--tries=1", "--spider", "http://localhost/health"]
      interval: 30s
      timeout: 10s
      retries: 3

  db:
    image: mysql:8.0
    container_name: hr-budget-db
    restart: unless-stopped
    environment:
      MYSQL_DATABASE: ${DB_DATABASE}
      MYSQL_ROOT_PASSWORD: ${DB_PASSWORD}
      MYSQL_PASSWORD: ${DB_PASSWORD}
      MYSQL_USER: ${DB_USERNAME}
    ports:
      - "3306:3306"
    volumes:
      - dbdata:/var/lib/mysql
    networks:
      - hr-budget-network
    healthcheck:
      test: ["CMD", "mysqladmin", "ping", "-h", "localhost", "-u", "root", "-p${DB_PASSWORD}"]
      interval: 30s
      timeout: 10s
      retries: 3
      start_period: 60s

networks:
  hr-budget-network:
    driver: bridge

volumes:
  dbdata:
    driver: local
```

## 🚀 CI/CD Pipelines

### GitHub Actions

Create `.github/workflows/main.yml`:

```yaml
name: CI/CD Pipeline

on:
  push:
    branches: [ "main" ]
  pull_request:
    branches: [ "main" ]

jobs:
  test:
    runs-on: ubuntu-latest
    
    steps:
    - uses: actions/checkout@v3
    
    - name: Setup PHP
      uses: shivammathur/setup-php@v2
      with:
        php-version: '8.3'
        
    - name: Install Dependencies
      run: composer install -q --no-ansi --no-interaction --no-scripts --no-progress --prefer-dist
      
    - name: Run Tests
      run: vendor/bin/phpunit

  deploy:
    needs: test
    runs-on: ubuntu-latest
    if: github.ref == 'refs/heads/main'
    
    steps:
    - name: Deploy to Production
      uses: appleboy/ssh-action@master
      with:
        host: ${{ secrets.HOST }}
        username: ${{ secrets.USERNAME }}
        key: ${{ secrets.SSH_KEY }}
        script: |
          cd /var/www/hr_budget
          git pull origin main
          composer install --no-dev --optimize-autoloader
          
          # Run database migrations
          cd database/migrations
          ./run_migrations.sh
          
          # Clear PHP Opcache (if enabled)
          sudo service php8.3-fpm reload
```

## 🔐 Secrets Management

1.  **Never commit `.env` files.**
2.  Use GitHub Secrets for sensitive data:
    - `DB_PASSWORD`
    - `SSH_KEY`
    - `HOST_IP`
3.  Inject secrets during build or via environment variables in `docker-compose.yml`.

## 🔄 Deployment Automation

### Zero-Downtime Deployment (Concept)

1.  **Blue/Green Deployment**:
    - Run two identical environments (Blue = Live, Green = New).
    - Deploy to Green.
    - Switch Load Balancer to Green.
    - Turn off Blue.

2.  **Rolling Updates**:
    - Update one container at a time.
    - Docker Compose supports this via `deploy.update_config`.

### Production Checklist
- [x] Use multi-stage builds to reduce image size.
- [x] Add health checks to all services.
- [ ] Disable X-Powered-By headers in Nginx.
- [ ] Use specific caching headers for static assets.
- [ ] Ensure database backups are automated before deployment.
- [ ] Set up log rotation for container logs.
- [ ] Configure resource limits (CPU/Memory) in docker-compose.
