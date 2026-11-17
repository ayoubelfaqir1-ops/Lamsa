# Production Deployment & Provisioning Checklist

> Step-by-step production server setup, software provisioning, SSL certificate installation, and zero-downtime deployment guide for Lamsa.ma.

---

## 1. Target Infrastructure Requirements

- **Operating System:** Ubuntu 22.04 LTS or Ubuntu 24.04 LTS
- **Compute:** 2 vCPU, 4GB RAM minimum (8GB recommended for live auctions)
- **Software Stack:**
  - Nginx (Web Server / Reverse Proxy)
  - PHP 8.2-FPM (with `opcache`, `mysql`, `redis`, `gd`, `bcmath`, `curl`, `mbstring`, `xml`, `zip`)
  - MySQL 8.0 or AWS RDS MySQL
  - Redis 7.x
  - Supervisor (Daemon manager for queues and WebSockets)
  - Certbot (Let's Encrypt SSL)

---

## 2. Server Provisioning Steps (Ubuntu)

### Step 1: Install Required Packages
```bash
sudo apt update && sudo apt upgrade -y
sudo apt install -y nginx redis-server supervisor certbot python3-certbot-nginx git unzip

# Install PHP 8.2 and extensions
sudo add-apt-repository ppa:ondrej/php -y
sudo apt update
sudo apt install -y php8.2-fpm php8.2-cli php8.2-mysql php8.2-redis php8.2-curl \
    php8.2-gd php8.2-mbstring php8.2-xml php8.2-zip php8.2-bcmath
```

### Step 2: Clone Codebase & Install Composer
```bash
sudo mkdir -p /var/www/Lamsa.ma
sudo chown -R $USER:www-data /var/www/Lamsa.ma

git clone https://github.com/ayoubelfaqir1-ops/Lamsa.git /var/www/Lamsa.ma
cd /var/www/Lamsa.ma

# Install production dependencies only (no dev dependencies)
composer install --no-dev --optimize-autoloader --no-interaction
```

### Step 3: Configure Permissions
```bash
sudo chown -R www-data:www-data /var/www/Lamsa.ma/storage /var/www/Lamsa.ma/bootstrap/cache
sudo chmod -R 775 /var/www/Lamsa.ma/storage /var/www/Lamsa.ma/bootstrap/cache
```

### Step 4: Configure Production `.env`
```bash
cp .env.production.example .env
php artisan key:generate
nano .env
```
Fill in production database credentials, Redis host, AWS S3 keys, and mail credentials.

### Step 5: Run Database Migrations
```bash
php artisan migrate --force
```

---

## 3. Web Server & SSL Configuration (Nginx)

### Step 1: Copy Nginx Virtual Host Template
```bash
sudo cp deployment/nginx/lamsa.conf.example /etc/nginx/sites-available/lamsa.conf
sudo cp deployment/nginx/security-headers.conf /etc/nginx/snippets/security-headers.conf

# Enable site configuration
sudo ln -s /etc/nginx/sites-available/lamsa.conf /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl reload nginx
```

### Step 2: Issue Free Let's Encrypt SSL Certificate
```bash
sudo certbot --nginx -d api.lamsa.ma
```
Certbot will automatically modify the Nginx config to enable HTTPS on port 443 with automated certificate renewal.

---

## 4. Background Workers & Daemons (Supervisor)

Supervisor keeps queue workers and Laravel Reverb running 24/7, automatically restarting them if they crash.

### Step 1: Copy Supervisor Config
```bash
sudo cp deployment/supervisor/lamsa-worker.conf.example /etc/supervisor/conf.d/lamsa-worker.conf
```

### Step 2: Reload Supervisor
```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start all
sudo supervisorctl status
```
Expected output:
```text
lamsa-worker:lamsa-worker_00   RUNNING   pid 18234, uptime 0:01:15
lamsa-worker:lamsa-worker_01   RUNNING   pid 18235, uptime 0:01:15
```

---

## 5. Production Optimization Commands

Run these Laravel performance optimizations on the server after every deployment:

```bash
# Cache configuration files into a single array
php artisan config:cache

# Cache routes for ultra-fast matching
php artisan route:cache

# Cache compiled Blade views (if any)
php artisan view:cache

# Restart queue workers to load newly deployed code
php artisan queue:restart
```

---

## 6. Live Post-Deploy Verification

Verify that all subsystems are alive and healthy:

```bash
curl -I https://api.lamsa.ma/api/v1/health
```
Must return:
```http
HTTP/2 200 
content-type: application/json
```
```json
{
  "status": "healthy",
  "checks": {
    "database": { "status": "up" },
    "cache": { "status": "up" },
    "storage": { "status": "up" }
  }
}
```

