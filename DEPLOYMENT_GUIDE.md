# Task Manager - Deployment Guide

## Table of Contents
1. [Pre-Deployment Checklist](#pre-deployment-checklist)
2. [Option 1: Laravel Forge (Recommended - Easiest)](#option-1-laravel-forge-recommended)
3. [Option 2: Manual VPS Deployment](#option-2-manual-vps-deployment)
4. [Option 3: Shared Hosting](#option-3-shared-hosting)
5. [Post-Deployment Setup](#post-deployment-setup)
6. [SSL Certificate](#ssl-certificate)
7. [Domain Configuration](#domain-configuration)

---

## Pre-Deployment Checklist

### 1. Prepare Your Code

```bash
# Make sure all changes are committed
git add .
git commit -m "Prepare for deployment"

# Create production branch (optional)
git checkout -b production
```

### 2. Update Environment for Production

Create a `.env.production` file:

```env
APP_NAME="Task Manager"
APP_ENV=production
APP_KEY=base64:YOUR_NEW_KEY_HERE
APP_DEBUG=false
APP_URL=https://yourdomain.com

# Database (will be configured on server)
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=your_database
DB_USERNAME=your_username
DB_PASSWORD=your_secure_password

# Queue - MUST use Redis in production
QUEUE_CONNECTION=redis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

# Mail (use your production mail service)
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your@email.com
MAIL_PASSWORD=your_app_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=your@email.com
MAIL_FROM_NAME="${APP_NAME}"

# WhatsApp
WHATSAPP_PROVIDER=meta
WHATSAPP_USE_TEMPLATES=true
META_WHATSAPP_TOKEN=your_production_token
META_WHATSAPP_PHONE_ID=your_phone_id
META_WHATSAPP_BUSINESS_ID=your_business_id
META_WHATSAPP_VERSION=v21.0

# Session
SESSION_DRIVER=redis
SESSION_LIFETIME=120

# Cache
CACHE_DRIVER=redis
```

### 3. Security Checklist

- [ ] Generate new `APP_KEY` for production
- [ ] Set `APP_DEBUG=false`
- [ ] Use strong database passwords
- [ ] Enable HTTPS/SSL
- [ ] Configure CORS properly
- [ ] Set up firewall rules

---

## Option 1: Laravel Forge (Recommended)

**Best for:** Quick deployment with minimal server management
**Cost:** $12/month (Forge) + $6-12/month (DigitalOcean droplet)
**Time:** 15-30 minutes

### Step 1: Sign Up for Services

1. **Laravel Forge:** https://forge.laravel.com
   - Sign up for Forge ($12/month)

2. **Server Provider** (choose one):
   - DigitalOcean (recommended): https://digitalocean.com
   - Vultr: https://vultr.com
   - Linode: https://linode.com

### Step 2: Create Server on Forge

1. Log in to Laravel Forge
2. Click **Create Server**
3. Select your provider (DigitalOcean)
4. Configure server:
   - **Server Name:** task-manager-prod
   - **Size:** Basic ($6/month - 1GB RAM) or Standard ($12/month - 2GB RAM recommended)
   - **Region:** Choose closest to your users
   - **PHP Version:** 8.3
   - **Database:** MySQL
   - Click **Create Server**

Wait 5-10 minutes for server provisioning.

### Step 3: Deploy Your Site

1. In Forge, click **New Site**
2. Enter your domain: `yourdomain.com`
3. **Root Domain:** Check this
4. **Project Type:** General PHP/Laravel
5. Click **Add Site**

### Step 4: Connect Git Repository

1. Go to your site in Forge
2. Click **Git Repository**
3. Enter:
   - **Provider:** GitHub/GitLab/Bitbucket
   - **Repository:** yourusername/task-scheduling-system
   - **Branch:** main (or production)
4. Click **Install Repository**

### Step 5: Configure Environment

1. Click **Environment**
2. Replace with your production `.env` values
3. Make sure to generate a new `APP_KEY`:
   ```bash
   php artisan key:generate --show
   ```

### Step 6: Deploy

1. Click **Deploy Now**
2. Wait for deployment to complete

### Step 7: Set Up Queue Worker

1. Go to **Daemons** tab
2. Click **New Daemon**
3. Configure:
   - **Command:** `php artisan queue:work redis --sleep=3 --tries=3 --max-time=3600`
   - **User:** forge
   - **Directory:** /home/forge/yourdomain.com
4. Click **Create Daemon**

### Step 8: Set Up Scheduler

1. Enable the Laravel Scheduler (auto-enabled in Forge)
2. Verify in **Scheduler** tab

### Step 9: Install Redis

1. Go to **Redis** tab
2. Click **Install Redis**
3. Wait for installation

### Step 10: SSL Certificate

1. Go to **SSL** tab
2. Click **LetsEncrypt**
3. Enter your email
4. Click **Obtain Certificate**

**Done!** Your site is live at `https://yourdomain.com`

---

## Option 2: Manual VPS Deployment

**Best for:** Full control, learning, cost savings
**Cost:** $6-12/month
**Time:** 1-2 hours

### Step 1: Create a VPS

1. Sign up at DigitalOcean/Vultr/Linode
2. Create a droplet:
   - **OS:** Ubuntu 22.04 LTS
   - **Size:** 2GB RAM minimum
   - **Region:** Closest to users
   - **SSH Key:** Add your SSH key

### Step 2: Initial Server Setup

SSH into your server:

```bash
ssh root@your_server_ip
```

Update system:

```bash
apt update && apt upgrade -y
```

Create deploy user:

```bash
adduser deployer
usermod -aG sudo deployer
su - deployer
```

### Step 3: Install Dependencies

```bash
# Install PHP 8.3 and extensions
sudo apt install -y software-properties-common
sudo add-apt-repository ppa:ondrej/php
sudo apt update
sudo apt install -y php8.3 php8.3-fpm php8.3-cli php8.3-mysql php8.3-redis \
    php8.3-mbstring php8.3-xml php8.3-bcmath php8.3-curl php8.3-zip \
    php8.3-gd php8.3-intl

# Install Composer
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer

# Install MySQL
sudo apt install -y mysql-server
sudo mysql_secure_installation

# Install Redis
sudo apt install -y redis-server
sudo systemctl enable redis-server

# Install Nginx
sudo apt install -y nginx
sudo systemctl enable nginx

# Install Node.js (for building assets)
curl -fsSL https://deb.nodesource.com/setup_20.x | sudo -E bash -
sudo apt install -y nodejs

# Install Supervisor (for queue workers)
sudo apt install -y supervisor
sudo systemctl enable supervisor
```

### Step 4: Configure MySQL

```bash
sudo mysql

CREATE DATABASE task_manager;
CREATE USER 'taskuser'@'localhost' IDENTIFIED BY 'your_secure_password';
GRANT ALL PRIVILEGES ON task_manager.* TO 'taskuser'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

### Step 5: Deploy Application

```bash
# Clone repository
cd /var/www
sudo mkdir task-manager
sudo chown deployer:deployer task-manager
git clone https://github.com/yourusername/task-scheduling-system.git task-manager
cd task-manager

# Install dependencies
composer install --optimize-autoloader --no-dev
npm install
npm run build

# Set permissions
sudo chown -R deployer:www-data storage bootstrap/cache
sudo chmod -R 775 storage bootstrap/cache

# Configure environment
cp .env.example .env
nano .env  # Edit with production values

# Generate key
php artisan key:generate

# Run migrations
php artisan migrate --force

# Optimize
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### Step 6: Configure Nginx

```bash
sudo nano /etc/nginx/sites-available/task-manager
```

Add:

```nginx
server {
    listen 80;
    server_name yourdomain.com www.yourdomain.com;
    root /var/www/task-manager/public;

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
        fastcgi_pass unix:/var/run/php/php8.3-fpm.sock;
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
sudo ln -s /etc/nginx/sites-available/task-manager /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl reload nginx
```

### Step 7: Set Up Queue Worker

```bash
sudo nano /etc/supervisor/conf.d/task-manager-worker.conf
```

Add:

```ini
[program:task-manager-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/task-manager/artisan queue:work redis --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=deployer
numprocs=2
redirect_stderr=true
stdout_logfile=/var/www/task-manager/storage/logs/worker.log
stopwaitsecs=3600
```

Start worker:

```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start task-manager-worker:*
```

### Step 8: Set Up Cron (Scheduler)

```bash
crontab -e
```

Add:

```cron
* * * * * cd /var/www/task-manager && php artisan schedule:run >> /dev/null 2>&1
```

### Step 9: Install SSL with Let's Encrypt

```bash
sudo apt install -y certbot python3-certbot-nginx
sudo certbot --nginx -d yourdomain.com -d www.yourdomain.com
```

**Done!** Your site is live at `https://yourdomain.com`

---

## Option 3: Shared Hosting (cPanel)

**Best for:** Budget-friendly, simple sites
**Cost:** $3-10/month
**Limitations:** No queue workers (use sync queue), limited control

### Requirements

- PHP 8.1+
- MySQL database
- SSH access (recommended) or File Manager
- Composer support

### Step 1: Prepare Hosting

1. Create a MySQL database via cPanel
2. Note down database credentials

### Step 2: Upload Files

**Option A: Via SSH**
```bash
ssh username@yourdomain.com
cd public_html
git clone https://github.com/yourusername/task-scheduling-system.git .
composer install --optimize-autoloader --no-dev
```

**Option B: Via File Manager**
1. Upload ZIP of your project
2. Extract to `public_html`

### Step 3: Configure

```bash
# Copy environment
cp .env.example .env
nano .env
```

**Important changes for shared hosting:**
```env
APP_ENV=production
APP_DEBUG=false
QUEUE_CONNECTION=sync  # No background workers on shared hosting
SESSION_DRIVER=file
CACHE_DRIVER=file
```

```bash
# Generate key
php artisan key:generate

# Run migrations
php artisan migrate --force

# Optimize
php artisan optimize
```

### Step 4: Configure .htaccess

Make sure `public/.htaccess` exists with:

```apache
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteRule ^(.*)$ public/$1 [L]
</IfModule>
```

### Step 5: Point Domain

If domain root is `public_html`:
- Move contents of `public` folder to `public_html`
- Update `index.php` paths accordingly

Or set document root to `public_html/public` in cPanel.

**Limitations:**
- No real-time queue workers (WhatsApp messages sent synchronously)
- Slower performance
- Limited to cron jobs for scheduler

---

## Post-Deployment Setup

### 1. Test Your Application

Visit your domain and verify:
- [ ] Homepage loads
- [ ] Login works
- [ ] Create task works
- [ ] WhatsApp test works
- [ ] Email works

### 2. Monitor Logs

```bash
# Application logs
tail -f storage/logs/laravel.log

# Queue worker logs (if using Forge/VPS)
tail -f storage/logs/worker.log

# Nginx logs
sudo tail -f /var/log/nginx/error.log
```

### 3. Set Up Monitoring

**Free Options:**
- UptimeRobot: https://uptimerobot.com
- Better Uptime: https://betteruptime.com

**Paid Options:**
- Laravel Pulse (built-in)
- Sentry: https://sentry.io

### 4. Backup Strategy

**Forge:**
- Enable automated backups in Forge dashboard

**Manual:**
```bash
# Database backup
mysqldump -u user -p database > backup_$(date +%Y%m%d).sql

# Automate with cron
0 2 * * * mysqldump -u user -p database > /backups/db_$(date +\%Y\%m\%d).sql
```

---

## Domain Configuration

### Pointing Domain to Server

**Nameservers (recommended):**
1. Get nameservers from your host:
   - DigitalOcean: `ns1.digitalocean.com`, `ns2.digitalocean.com`
   - Cloudflare: `ns1.cloudflare.com`, `ns2.cloudflare.com`
2. Update at your domain registrar (GoDaddy, Namecheap, etc.)

**A Record:**
1. Go to DNS settings
2. Create A record:
   - Type: A
   - Name: @
   - Value: Your server IP
   - TTL: 3600
3. Create A record for www:
   - Type: A
   - Name: www
   - Value: Your server IP

Wait 1-24 hours for DNS propagation.

---

## Troubleshooting

### Issue: 500 Error

```bash
# Check permissions
sudo chown -R deployer:www-data storage bootstrap/cache
sudo chmod -R 775 storage bootstrap/cache

# Check logs
tail -f storage/logs/laravel.log

# Clear cache
php artisan cache:clear
php artisan config:clear
php artisan view:clear
```

### Issue: Queue Not Processing

```bash
# Check supervisor
sudo supervisorctl status

# Restart queue workers
sudo supervisorctl restart task-manager-worker:*

# Check logs
tail -f storage/logs/worker.log
```

### Issue: WhatsApp Not Sending

```bash
# Check queue connection
php artisan queue:failed

# Test WhatsApp
php artisan whatsapp:test +263783017279

# Check logs
grep "WhatsApp" storage/logs/laravel.log
```

---

## Quick Start Commands

### Laravel Forge
```bash
# Just push to Git
git push origin main

# Forge auto-deploys (if enabled)
```

### Manual VPS
```bash
# Connect via SSH
ssh deployer@your_server_ip

# Navigate to project
cd /var/www/task-manager

# Pull latest changes
git pull origin main

# Update dependencies
composer install --optimize-autoloader --no-dev

# Run migrations
php artisan migrate --force

# Clear and rebuild cache
php artisan optimize:clear
php artisan optimize

# Restart queue workers
sudo supervisorctl restart task-manager-worker:*
```

---

## Recommended Setup

For production, I recommend:

**Option 1 (Easiest):**
- Laravel Forge + DigitalOcean ($18/month)
- Cloudflare (free CDN + DNS)
- Automated backups

**Option 2 (Best Value):**
- DigitalOcean VPS ($12/month)
- Manual setup (follow Option 2)
- Cloudflare (free)

**Option 3 (Budget):**
- Shared hosting ($5/month)
- Limited features
- Good for testing/small teams

---

## Next Steps

1. Choose your deployment option
2. Follow the guide step-by-step
3. Test thoroughly
4. Set up monitoring
5. Configure backups
6. Document your setup

Good luck with your deployment! 🚀
