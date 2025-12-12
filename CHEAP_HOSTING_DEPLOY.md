# Deploy to Cheap Shared Hosting - Quick Guide

## Recommended Cheap Hosts ($1-5/month)

1. **Hostinger** - $1.99/month
   - https://hostinger.com
   - PHP 8+, MySQL, SSL included
   - Good performance

2. **Namecheap** - $2.88/month
   - https://namecheap.com
   - Easy cPanel
   - Free domain first year

3. **InfinityFree** - FREE
   - https://infinityfree.net
   - 100% free
   - Limited features

4. **000webhost** - FREE
   - https://000webhost.com
   - Free tier available

## Step-by-Step Deployment

### Step 1: Sign Up for Hosting

1. Choose a host (Hostinger recommended)
2. Sign up for cheapest plan
3. Note your cPanel login credentials

### Step 2: Create Database

1. Log in to cPanel
2. Go to **MySQL Databases**
3. Create database: `taskmanager_db`
4. Create user: `taskmanager_user`
5. Set strong password
6. Add user to database with ALL PRIVILEGES
7. Note down:
   - Database name
   - Username
   - Password
   - Host (usually `localhost`)

### Step 3: Prepare Your Files

On your local machine:

```bash
cd /home/kc/PhpstormProjects/smartwork/task-scheduling-system

# Install production dependencies
composer install --optimize-autoloader --no-dev

# Build assets
npm install
npm run build

# Create .env for production
cp .env .env.production
```

Edit `.env.production`:

```env
APP_NAME="Task Manager"
APP_ENV=production
APP_DEBUG=false
APP_URL=http://yourdomain.com

# Database (use values from Step 2)
DB_CONNECTION=mysql
DB_HOST=localhost
DB_DATABASE=youruser_taskmanager_db
DB_USERNAME=youruser_taskmanager
DB_PASSWORD=your_password

# IMPORTANT: Shared hosting can't run queue workers
QUEUE_CONNECTION=sync

# Session & Cache - use file (Redis not available)
SESSION_DRIVER=file
CACHE_DRIVER=file

# Mail
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your@email.com
MAIL_PASSWORD=your_app_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=your@email.com

# WhatsApp
WHATSAPP_PROVIDER=meta
WHATSAPP_USE_TEMPLATES=false
META_WHATSAPP_TOKEN=your_token
META_WHATSAPP_PHONE_ID=your_phone_id
META_WHATSAPP_BUSINESS_ID=your_business_id

# Sessions
SESSION_LIFETIME=120
```

Generate APP_KEY:
```bash
php artisan key:generate --show
```

Copy the generated key and add to `.env.production`:
```env
APP_KEY=base64:...your_generated_key...
```

### Step 4: Upload Files

**Method A: File Manager (Easier)**

1. Create ZIP of your project:
   ```bash
   # Exclude unnecessary files
   zip -r task-manager.zip . -x "node_modules/*" ".git/*" "tests/*" "*.md"
   ```

2. Log in to cPanel
3. Go to **File Manager**
4. Navigate to `public_html`
5. Upload `task-manager.zip`
6. Right-click → Extract
7. Delete the ZIP file

**Method B: FTP (Alternative)**

1. Get FTP credentials from cPanel
2. Use FileZilla or similar
3. Upload all files to `public_html`

### Step 5: Configure Files

In cPanel File Manager:

1. Rename `.env.production` to `.env`

2. Edit `public_html/index.php` (if needed):
   - Make sure paths are correct

3. Create `.htaccess` in root (`public_html`):
   ```apache
   <IfModule mod_rewrite.c>
       RewriteEngine On
       RewriteRule ^(.*)$ public/$1 [L]
   </IfModule>
   ```

4. Ensure `public/.htaccess` exists:
   ```apache
   <IfModule mod_rewrite.c>
       <IfModule mod_negotiation.c>
           Options -MultiViews -Indexes
       </IfModule>

       RewriteEngine On

       # Handle Authorization Header
       RewriteCond %{HTTP:Authorization} .
       RewriteRule .* - [E=HTTP_AUTHORIZATION:%{HTTP:Authorization}]

       # Redirect Trailing Slashes...
       RewriteCond %{REQUEST_FILENAME} !-d
       RewriteCond %{REQUEST_URI} (.+)/$
       RewriteRule ^ %1 [L,R=301]

       # Send Requests To Front Controller...
       RewriteCond %{REQUEST_FILENAME} !-d
       RewriteCond %{REQUEST_FILENAME} !-f
       RewriteRule ^ index.php [L]
   </IfModule>
   ```

### Step 6: Set Permissions

In cPanel File Manager:

1. Select `storage` folder
2. Right-click → Change Permissions
3. Set to `755` (or `775` if needed)
4. Check "Recurse into subdirectories"
5. Click "Change Permissions"

6. Repeat for `bootstrap/cache`

### Step 7: Run Migrations

**Option A: Terminal in cPanel**

1. Go to cPanel → **Terminal** (or SSH Access)
2. Navigate to your site:
   ```bash
   cd public_html
   ```

3. Run migrations:
   ```bash
   php artisan migrate --force
   ```

4. Optimize:
   ```bash
   php artisan config:cache
   php artisan route:cache
   php artisan view:cache
   ```

**Option B: Create Migration Script**

Create `migrate.php` in `public_html`:

```php
<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->call('migrate', ['--force' => true]);
echo "Migrations completed!";
```

Visit `http://yourdomain.com/migrate.php` in browser.
**Delete this file after use!**

### Step 8: Test Your Site

1. Visit `http://yourdomain.com`
2. You should see the login page
3. Try logging in
4. Test creating a task

### Step 9: Get Free SSL

In cPanel:

1. Go to **SSL/TLS Status**
2. Click **Run AutoSSL**
3. Wait for SSL certificate to install
4. Your site will now be `https://yourdomain.com`

---

## Important Limitations of Cheap Hosting

### ⚠️ No Background Queue Workers

Shared hosting can't run queue workers, so:

- **WhatsApp messages sent immediately** (may slow down page load)
- **Emails sent immediately** (may slow down page load)
- Use `QUEUE_CONNECTION=sync` in `.env`

### ⚠️ No Redis

- Use `CACHE_DRIVER=file`
- Use `SESSION_DRIVER=file`

### ⚠️ Limited Cron Jobs

- May only run every hour (not every minute)
- Task scheduler may not work as expected

---

## Troubleshooting

### 500 Internal Server Error

1. Check `.htaccess` files exist
2. Check permissions on `storage` and `bootstrap/cache`
3. Check error logs in cPanel

### Database Connection Error

1. Verify database credentials in `.env`
2. Make sure database host is `localhost`
3. Check database user has permissions

### Page Not Found (404)

1. Check `.htaccess` in root and `public` folder
2. Enable mod_rewrite in cPanel (usually enabled)

### Assets Not Loading (CSS/JS)

1. Run `npm run build` locally before uploading
2. Check `public` folder has `build` directory
3. Verify `APP_URL` in `.env` matches your domain

---

## Update/Deploy New Changes

When you make changes:

1. **Local:**
   ```bash
   git add .
   git commit -m "Your changes"

   # Build assets
   npm run build

   # Create ZIP
   zip -r update.zip app resources public/build routes .env.production
   ```

2. **Upload to server:**
   - Upload `update.zip` to cPanel
   - Extract (overwrite files)
   - Delete ZIP

3. **Run migrations** (if any):
   ```bash
   php artisan migrate --force
   ```

4. **Clear cache:**
   ```bash
   php artisan cache:clear
   php artisan config:clear
   php artisan view:clear
   ```

---

## Estimated Costs

| Item | Cost |
|------|------|
| Hosting (Hostinger) | $1.99-2.99/month |
| Domain (if not included) | $10-15/year |
| SSL Certificate | FREE (Let's Encrypt) |
| **Total** | **~$2-3/month** |

---

## Alternative: FREE Hosting

### InfinityFree (100% Free)

**Pros:**
- Completely free
- PHP 8+, MySQL
- Free SSL

**Cons:**
- Ads on free plan
- Limited resources
- May suspend inactive accounts

**Setup:**
1. Sign up at https://infinityfree.net
2. Create account
3. Follow same steps as above
4. Use their subdomain or connect your domain

---

## Next Steps

1. ✅ Choose a cheap host (Hostinger recommended)
2. ✅ Create account and database
3. ✅ Upload files
4. ✅ Configure `.env`
5. ✅ Run migrations
6. ✅ Test your site
7. ✅ Enable SSL

**Your site should be live for ~$2/month!** 🎉

---

## Quick Deployment Checklist

- [ ] Signed up for hosting
- [ ] Created database
- [ ] Updated `.env` with database credentials
- [ ] Set `QUEUE_CONNECTION=sync`
- [ ] Generated new `APP_KEY`
- [ ] Uploaded files to `public_html`
- [ ] Set permissions on `storage` and `bootstrap/cache`
- [ ] Ran `php artisan migrate --force`
- [ ] Cleared cache (`php artisan optimize`)
- [ ] Tested login
- [ ] Enabled SSL
- [ ] Domain pointing correctly