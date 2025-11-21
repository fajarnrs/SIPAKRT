# 🚀 Deployment Guide - Data Warga

Panduan lengkap deployment aplikasi Data Warga ke berbagai environment production.

## 📋 Table of Contents

1. [cPanel Deployment](#cpanel-deployment)
2. [VPS Deployment (Ubuntu/Debian)](#vps-deployment)
3. [Docker Production](#docker-production)
4. [Post-Deployment Checklist](#post-deployment-checklist)

---

## 🌐 cPanel Deployment

### Prerequisites

- cPanel account dengan SSH access
- PHP 8.1+
- MySQL 8.0+
- PHP Extensions: zip, mbstring, pdo, gd, xml

### Step-by-Step

#### 1. Prepare Files

```bash
# Di local/development
cd data-warga
tar -czf data-warga-deploy.tar.gz \
  --exclude='.git' \
  --exclude='node_modules' \
  --exclude='.env' \
  --exclude='storage/logs/*' \
  .
```

#### 2. Upload to cPanel

- Upload `data-warga-deploy.tar.gz` via File Manager atau FTP
- Extract di folder aplikasi (misal: `/home/username/public_html`)

#### 3. Setup via SSH

```bash
# Login SSH
ssh username@your-domain.com

cd public_html

# Extract (jika belum)
tar -xzf data-warga-deploy.tar.gz

# Setup environment
cp .env.example .env
nano .env  # Edit database credentials

# Install dependencies
composer install --no-dev --optimize-autoloader --no-scripts

# Or upload vendor backup
tar -xzf vendor-backup.tar.gz
```

#### 4. Database Setup

Via **phpMyAdmin**:
1. Create database: `pendataan_warga`
2. Import migrations atau jalankan manual SQL

Via **SSH**:
```bash
# Create database
mysql -u username -p -e "CREATE DATABASE pendataan_warga CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

# Run migrations
php artisan migrate --force

# Or manual SQL
mysql -u username -p pendataan_warga < database/migrations.sql
```

#### 5. Configuration

```bash
# Generate app key
php artisan key:generate

# Clear bootstrap cache
rm -rf bootstrap/cache/*.php

# Set permissions
chmod -R 755 storage bootstrap/cache
chown -R username:username storage bootstrap/cache

# Storage link
php artisan storage:link
```

#### 6. Repair Existing Data

```bash
# Fix KK tanpa resident kepala keluarga
php scripts/repair-missing-head-residents.php
```

#### 7. Create Admin User

```bash
php artisan make:filament-user
```

### Troubleshooting cPanel

**❌ Error: `proc_open not available`**
```bash
# Solusi 1: Upload vendor backup
tar -xzf vendor-backup.tar.gz

# Solusi 2: Use PHP with less restrictions
php -d disable_functions= artisan migrate
```

**❌ Error: `Class HouseholdObserver not found`**
```bash
# Manual clear cache
rm -rf bootstrap/cache/*.php
composer dump-autoload --no-scripts
```

**❌ Error: Permission denied**
```bash
chmod -R 755 storage bootstrap/cache
chown -R username:username storage bootstrap/cache
```

---

## 🖥️ VPS Deployment (Ubuntu/Debian)

### Prerequisites

- Ubuntu 20.04+ atau Debian 11+
- Root or sudo access
- Domain pointing to VPS

### 1. Install Dependencies

```bash
# Update system
sudo apt update && sudo apt upgrade -y

# Install PHP 8.1
sudo apt install -y software-properties-common
sudo add-apt-repository ppa:ondrej/php
sudo apt update
sudo apt install -y php8.1 php8.1-fpm php8.1-mysql php8.1-mbstring \
    php8.1-xml php8.1-gd php8.1-curl php8.1-zip php8.1-bcmath

# Install Composer
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer

# Install MySQL
sudo apt install -y mysql-server
sudo mysql_secure_installation

# Install Nginx
sudo apt install -y nginx
```

### 2. Setup Database

```bash
sudo mysql

CREATE DATABASE pendataan_warga CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'datawarga'@'localhost' IDENTIFIED BY 'strong_password';
GRANT ALL PRIVILEGES ON pendataan_warga.* TO 'datawarga'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

### 3. Deploy Application

```bash
# Create directory
sudo mkdir -p /var/www/data-warga
cd /var/www/data-warga

# Clone or upload
git clone https://github.com/yourusername/data-warga.git .

# Or upload tar.gz and extract
scp data-warga-deploy.tar.gz user@vps:/var/www/data-warga/
tar -xzf data-warga-deploy.tar.gz

# Install dependencies
composer install --no-dev --optimize-autoloader

# Setup environment
cp .env.example .env
nano .env
```

Edit `.env`:
```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-domain.com

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=pendataan_warga
DB_USERNAME=datawarga
DB_PASSWORD=strong_password
```

```bash
# Generate key
php artisan key:generate

# Run migrations
php artisan migrate --force

# Set permissions
sudo chown -R www-data:www-data /var/www/data-warga
sudo chmod -R 755 /var/www/data-warga/storage
sudo chmod -R 755 /var/www/data-warga/bootstrap/cache

# Storage link
php artisan storage:link

# Optimize
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### 4. Configure Nginx

```bash
sudo nano /etc/nginx/sites-available/data-warga
```

Add configuration:
```nginx
server {
    listen 80;
    server_name your-domain.com;
    root /var/www/data-warga/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    index index.php;

    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.1-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

Enable site:
```bash
sudo ln -s /etc/nginx/sites-available/data-warga /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl restart nginx
```

### 5. Setup SSL (Let's Encrypt)

```bash
sudo apt install -y certbot python3-certbot-nginx
sudo certbot --nginx -d your-domain.com
```

### 6. Setup Supervisor (Optional - for Queue)

```bash
sudo apt install -y supervisor

sudo nano /etc/supervisor/conf.d/data-warga.conf
```

Add:
```ini
[program:data-warga-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/data-warga/artisan queue:work --sleep=3 --tries=3
autostart=true
autorestart=true
user=www-data
numprocs=1
redirect_stderr=true
stdout_logfile=/var/www/data-warga/storage/logs/worker.log
```

```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start data-warga-worker:*
```

---

## 🐳 Docker Production

### Prerequisites

- Docker 20.10+
- Docker Compose 2.0+
- Domain pointing to server

### 1. Prepare Environment

```bash
# Clone repository
git clone https://github.com/yourusername/data-warga.git
cd data-warga

# Setup environment
cp .env.example .env
nano .env
```

Edit `.env` for production:
```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-domain.com

DB_CONNECTION=mysql
DB_HOST=db
DB_PORT=3306
DB_DATABASE=pendataan_warga
DB_USERNAME=datawarga
DB_PASSWORD=strong_random_password
```

### 2. Production Docker Compose

Create `docker-compose.prod.yml`:

```yaml
version: '3.8'

services:
  app:
    build:
      context: .
      dockerfile: Dockerfile
    image: data-warga:latest
    container_name: data-warga-app
    restart: always
    volumes:
      - ./storage:/var/www/storage
      - ./public/storage:/var/www/public/storage
    environment:
      - APP_ENV=production
      - APP_DEBUG=false
    depends_on:
      - db
    networks:
      - data-warga-network

  db:
    image: mysql:8.0
    container_name: data-warga-db
    restart: always
    environment:
      MYSQL_DATABASE: ${DB_DATABASE}
      MYSQL_ROOT_PASSWORD: ${DB_PASSWORD}
      MYSQL_USER: ${DB_USERNAME}
      MYSQL_PASSWORD: ${DB_PASSWORD}
    volumes:
      - dbdata:/var/lib/mysql
    networks:
      - data-warga-network

  nginx:
    image: nginx:alpine
    container_name: data-warga-nginx
    restart: always
    ports:
      - "80:80"
      - "443:443"
    volumes:
      - ./:/var/www
      - ./deploy/nginx-prod.conf:/etc/nginx/conf.d/default.conf
      - ./ssl:/etc/nginx/ssl
    depends_on:
      - app
    networks:
      - data-warga-network

networks:
  data-warga-network:
    driver: bridge

volumes:
  dbdata:
```

### 3. Deploy

```bash
# Build and start
docker-compose -f docker-compose.prod.yml up -d --build

# Run migrations
docker-compose -f docker-compose.prod.yml exec app php artisan migrate --force

# Create admin
docker-compose -f docker-compose.prod.yml exec app php artisan make:filament-user

# Optimize
docker-compose -f docker-compose.prod.yml exec app php artisan optimize
```

### 4. Setup Reverse Proxy (Nginx/Traefik)

Use external Nginx or Traefik for SSL termination and load balancing.

---

## ✅ Post-Deployment Checklist

### Security

- [ ] Change default admin password
- [ ] Update `.env` with strong passwords
- [ ] Set `APP_DEBUG=false`
- [ ] Set `APP_ENV=production`
- [ ] Configure firewall (ufw/iptables)
- [ ] Setup SSL certificate
- [ ] Disable directory listing
- [ ] Set proper file permissions (755/644)

### Performance

- [ ] Run `php artisan optimize`
- [ ] Run `php artisan config:cache`
- [ ] Run `php artisan route:cache`
- [ ] Run `php artisan view:cache`
- [ ] Enable OPcache
- [ ] Configure Redis/Memcached (optional)

### Monitoring

- [ ] Setup application logging
- [ ] Configure error reporting
- [ ] Setup uptime monitoring
- [ ] Database backup schedule
- [ ] Monitor disk space
- [ ] Setup health check endpoint

### Backup

```bash
# Database backup
mysqldump -u username -p pendataan_warga > backup-$(date +%Y%m%d).sql

# Files backup
tar -czf files-backup-$(date +%Y%m%d).tar.gz \
  storage/app \
  public/storage \
  .env

# Automate with cron
0 2 * * * /path/to/backup-script.sh
```

### Updates

```bash
# Pull latest code
git pull origin main

# Update dependencies
composer install --no-dev --optimize-autoloader

# Run migrations
php artisan migrate --force

# Clear cache
php artisan optimize:clear
php artisan optimize

# Restart services
sudo systemctl restart php8.1-fpm nginx
```

---

## 🔒 Security Best Practices

1. **Never commit `.env` file**
2. **Use strong database passwords**
3. **Keep PHP and dependencies updated**
4. **Enable HTTPS only**
5. **Implement rate limiting**
6. **Regular security audits**
7. **Monitor logs for suspicious activity**
8. **Backup regularly**

---

## 📊 Monitoring & Logs

```bash
# Application logs
tail -f storage/logs/laravel.log

# Nginx logs
tail -f /var/log/nginx/error.log
tail -f /var/log/nginx/access.log

# PHP-FPM logs
tail -f /var/log/php8.1-fpm.log

# System logs
journalctl -u nginx -f
```

---

## 🆘 Emergency Rollback

```bash
# Restore database
mysql -u username -p pendataan_warga < backup-20251112.sql

# Restore files
tar -xzf files-backup-20251112.tar.gz

# Restart services
sudo systemctl restart php8.1-fpm nginx
```

---

## 📧 Support

Untuk masalah deployment, silakan buka issue di GitHub atau hubungi tim development.

---

**Happy Deploying! 🚀**
