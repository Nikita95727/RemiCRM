# 🚀 Production Deployment Guide

Complete guide for deploying Remi CRM to production servers.

## 📋 Server Requirements

### Minimum Specifications
- **CPU**: 2 cores
- **RAM**: 4GB
- **Storage**: 20GB SSD
- **OS**: Ubuntu 20.04+ / CentOS 8+ / Debian 11+

### Software Stack
- **Web Server**: Nginx 1.18+ or Apache 2.4+
- **PHP**: 8.2+ with FPM
- **Database**: MySQL 8.0+ or PostgreSQL 13+
- **Python**: 3.8+
- **Node.js**: 18+ (for asset building)
- **Supervisor**: For queue management
- **SSL**: Let's Encrypt or commercial certificate

## 🔧 Server Setup

### 1. Install Dependencies

**Ubuntu/Debian:**
```bash
# Update system
sudo apt update && sudo apt upgrade -y

# Install PHP and extensions
sudo apt install -y php8.2 php8.2-fpm php8.2-mysql php8.2-xml php8.2-curl \
    php8.2-mbstring php8.2-zip php8.2-gd php8.2-sqlite3 php8.2-redis

# Install web server
sudo apt install -y nginx

# Install database
sudo apt install -y mysql-server

# Install Python and Node.js
sudo apt install -y python3 python3-pip nodejs npm

# Install Composer
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer

# Install Supervisor
sudo apt install -y supervisor
```

### 2. Database Setup

```bash
# Secure MySQL installation
sudo mysql_secure_installation

# Create database and user
sudo mysql -u root -p
```

```sql
CREATE DATABASE remi_crm CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'remi_user'@'localhost' IDENTIFIED BY 'secure_password_here';
GRANT ALL PRIVILEGES ON remi_crm.* TO 'remi_user'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

### 3. Application Deployment

```bash
# Create application directory
sudo mkdir -p /var/www/remi-crm
sudo chown $USER:$USER /var/www/remi-crm

# Clone repository
cd /var/www
git clone <repository-url> remi-crm
cd remi-crm/crm-backend

# Install PHP dependencies
composer install --optimize-autoloader --no-dev

# Install Node.js dependencies and build assets
npm ci
npm run build

# Set permissions
sudo chown -R www-data:www-data /var/www/remi-crm
sudo chmod -R 755 /var/www/remi-crm
sudo chmod -R 775 /var/www/remi-crm/crm-backend/storage
sudo chmod -R 775 /var/www/remi-crm/crm-backend/bootstrap/cache
```

### 4. Environment Configuration

```bash
# Copy and configure environment
cp .env.example .env
nano .env
```

**Production .env settings:**
```env
APP_NAME="Remi CRM"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-domain.com

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=remi_crm
DB_USERNAME=remi_user
DB_PASSWORD=secure_password_here

# Cache & Session
CACHE_DRIVER=redis
SESSION_DRIVER=redis
QUEUE_CONNECTION=redis

# Redis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

# Mail (configure as needed)
MAIL_MAILER=smtp
MAIL_HOST=your-smtp-server.com
MAIL_PORT=587
MAIL_USERNAME=your-email@domain.com
MAIL_PASSWORD=your-email-password
MAIL_ENCRYPTION=tls

# Unipile Integration
UNIPILE_DSN=your_production_dsn
UNIPILE_TOKEN=your_production_token
UNIPILE_BASE_URL=https://api18.unipile.com:14862
```

```bash
# Generate application key
php artisan key:generate

# Cache configuration
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Run migrations
php artisan migrate --force

# Install Python dependencies
php artisan install:python-deps
```

## 🌐 Web Server Configuration

### Nginx Configuration

Create `/etc/nginx/sites-available/remi-crm`:

```nginx
server {
    listen 80;
    server_name your-domain.com www.your-domain.com;
    return 301 https://$server_name$request_uri;
}

server {
    listen 443 ssl http2;
    server_name your-domain.com www.your-domain.com;
    root /var/www/remi-crm/crm-backend/public;

    # SSL Configuration
    ssl_certificate /path/to/your/certificate.crt;
    ssl_certificate_key /path/to/your/private.key;
    ssl_protocols TLSv1.2 TLSv1.3;
    ssl_ciphers ECDHE-RSA-AES256-GCM-SHA512:DHE-RSA-AES256-GCM-SHA512:ECDHE-RSA-AES256-GCM-SHA384:DHE-RSA-AES256-GCM-SHA384;
    ssl_prefer_server_ciphers off;

    # Security Headers
    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-XSS-Protection "1; mode=block" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header Referrer-Policy "no-referrer-when-downgrade" always;
    add_header Content-Security-Policy "default-src 'self' http: https: data: blob: 'unsafe-inline'" always;

    index index.php;

    charset utf-8;

    # Handle Laravel routes
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    # PHP-FPM Configuration
    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
        fastcgi_hide_header X-Powered-By;
    }

    # Security
    location ~ /\.(?!well-known).* {
        deny all;
    }

    # Asset caching
    location ~* \.(js|css|png|jpg|jpeg|gif|ico|svg)$ {
        expires 1y;
        add_header Cache-Control "public, immutable";
    }

    # Gzip compression
    gzip on;
    gzip_vary on;
    gzip_min_length 1024;
    gzip_types text/plain text/css text/xml text/javascript application/javascript application/xml+rss application/json;
}
```

```bash
# Enable site
sudo ln -s /etc/nginx/sites-available/remi-crm /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl reload nginx
```

## 🔄 Queue Management

### Supervisor Configuration

Create `/etc/supervisor/conf.d/remi-crm-worker.conf`:

```ini
[program:remi-crm-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/remi-crm/crm-backend/artisan queue:work redis --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/var/www/remi-crm/crm-backend/storage/logs/worker.log
stopwaitsecs=3600
```

```bash
# Update supervisor
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start remi-crm-worker:*
```

## 📊 Monitoring & Maintenance

### Log Rotation

Create `/etc/logrotate.d/remi-crm`:

```
/var/www/remi-crm/crm-backend/storage/logs/*.log {
    daily
    missingok
    rotate 52
    compress
    delaycompress
    notifempty
    create 644 www-data www-data
    postrotate
        sudo supervisorctl restart remi-crm-worker:*
    endscript
}
```

### Cron Jobs

Add to crontab (`sudo crontab -e`):

```bash
# Laravel Scheduler
* * * * * cd /var/www/remi-crm/crm-backend && php artisan schedule:run >> /dev/null 2>&1

# Daily contact sync (adjust time as needed)
0 2 * * * cd /var/www/remi-crm/crm-backend && php artisan sync:contacts >> /var/log/remi-crm-sync.log 2>&1

# Weekly cleanup
0 3 * * 0 cd /var/www/remi-crm/crm-backend && php artisan queue:prune-failed --hours=168
```

### Health Checks

Create monitoring script `/var/www/remi-crm/health-check.sh`:

```bash
#!/bin/bash

# Check if application is responding
if ! curl -f -s http://localhost/health > /dev/null; then
    echo "Application not responding"
    # Send alert or restart services
fi

# Check queue workers
if ! supervisorctl status remi-crm-worker:* | grep -q RUNNING; then
    echo "Queue workers not running"
    supervisorctl restart remi-crm-worker:*
fi

# Check disk space
DISK_USAGE=$(df /var/www/remi-crm | tail -1 | awk '{print $5}' | sed 's/%//')
if [ $DISK_USAGE -gt 80 ]; then
    echo "Disk usage high: ${DISK_USAGE}%"
fi
```

## 🔒 Security Hardening

### Firewall Configuration

```bash
# UFW setup
sudo ufw default deny incoming
sudo ufw default allow outgoing
sudo ufw allow ssh
sudo ufw allow 'Nginx Full'
sudo ufw enable
```

### SSL Certificate (Let's Encrypt)

```bash
# Install Certbot
sudo apt install -y certbot python3-certbot-nginx

# Obtain certificate
sudo certbot --nginx -d your-domain.com -d www.your-domain.com

# Auto-renewal
sudo crontab -e
# Add: 0 12 * * * /usr/bin/certbot renew --quiet
```

### File Permissions

```bash
# Set secure permissions
sudo find /var/www/remi-crm -type f -exec chmod 644 {} \;
sudo find /var/www/remi-crm -type d -exec chmod 755 {} \;
sudo chmod -R 775 /var/www/remi-crm/crm-backend/storage
sudo chmod -R 775 /var/www/remi-crm/crm-backend/bootstrap/cache
sudo chmod 600 /var/www/remi-crm/crm-backend/.env
```

## 🚀 Deployment Script

Create automated deployment script `deploy.sh`:

```bash
#!/bin/bash

set -e

echo "🚀 Deploying Remi CRM..."

# Pull latest code
cd /var/www/remi-crm
git pull origin main

cd crm-backend

# Install/update dependencies
composer install --optimize-autoloader --no-dev
npm ci && npm run build

# Clear and cache
php artisan down
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan migrate --force

# Install Python deps if needed
php artisan install:python-deps

# Restart services
sudo supervisorctl restart remi-crm-worker:*
php artisan up

echo "✅ Deployment completed successfully!"
```

## 📈 Performance Optimization

### Redis Configuration

Add to `/etc/redis/redis.conf`:

```
maxmemory 256mb
maxmemory-policy allkeys-lru
```

### PHP-FPM Tuning

Edit `/etc/php/8.2/fpm/pool.d/www.conf`:

```ini
pm = dynamic
pm.max_children = 50
pm.start_servers = 5
pm.min_spare_servers = 5
pm.max_spare_servers = 35
pm.max_requests = 500
```

### Database Optimization

```sql
-- Add indexes for better performance
USE remi_crm;

CREATE INDEX idx_contacts_name ON contacts(name);
CREATE INDEX idx_contacts_email ON contacts(email);
CREATE INDEX idx_contacts_source ON contacts(source);
CREATE INDEX idx_contacts_tags ON contacts(tags);
CREATE INDEX idx_contacts_created_at ON contacts(created_at);

-- Optimize tables
OPTIMIZE TABLE contacts, integrated_accounts, contact_integrations;
```

## 🆘 Troubleshooting

### Common Issues

**Queue workers not processing:**
```bash
sudo supervisorctl status
sudo supervisorctl restart remi-crm-worker:*
tail -f /var/www/remi-crm/crm-backend/storage/logs/laravel.log
```

**High memory usage:**
```bash
# Check PHP memory limit
php -i | grep memory_limit

# Monitor processes
htop
```

**Database connection issues:**
```bash
# Test connection
mysql -u remi_user -p remi_crm

# Check MySQL status
sudo systemctl status mysql
```

**SSL certificate issues:**
```bash
# Check certificate
sudo certbot certificates

# Renew if needed
sudo certbot renew --dry-run
```

---

**For additional support, check the main [README.md](README.md) or create an issue with detailed logs.**
