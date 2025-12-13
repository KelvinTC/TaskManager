# Railway environment variables setup

This project is configured to deploy on Railway using the railway.toml at the repo root. Use this guide to set the minimum and recommended environment variables so the app boots correctly in production.

Note: APP_KEY is generated automatically during the build if not present (see railway.toml buildCommand). You can still set your own key if you prefer.

## Where to add variables in Railway
- Railway → Your Project → Variables
  - Project-level variables apply to all services unless overridden.
- Or per service: Railway → Service (web/worker) → Variables

Tip: Keep secrets (DB_PASSWORD, MAIL_PASSWORD, API tokens) only in Railway. Do not commit them to git.

## Minimal variables to go live
Set these first. Replace values in angle brackets.

```
APP_ENV=production
APP_DEBUG=false
APP_URL=https://<your-domain-or-railway-app-url>

# Database (match the Railway provisioned DB or your external DB)
DB_CONNECTION=mysql  # or pgsql
DB_HOST=<db-host>
DB_PORT=<3306-for-mysql or 5432-for-postgres>
DB_DATABASE=<db-name>
DB_USERNAME=<db-user>
DB_PASSWORD=<db-password>
```

APP_KEY: Not required to set manually; the build runs `php artisan key:generate --force` if no .env exists. If you want to set it yourself, generate locally (`php artisan key:generate --show`) and paste the value.

## Recommended production variables
Queues, cache, and sessions should use Redis in production.

```
# Cache/Queue/Session
CACHE_DRIVER=redis
QUEUE_CONNECTION=redis
SESSION_DRIVER=redis
SESSION_LIFETIME=120

# Redis (use your Railway Redis service values)
REDIS_HOST=<redis-host>
REDIS_PORT=6379
REDIS_PASSWORD=<redis-password-if-any>
```

Mail settings (for password resets/notifications):

```
MAIL_MAILER=smtp
MAIL_HOST=<smtp-host>
MAIL_PORT=587
MAIL_USERNAME=<smtp-user>
MAIL_PASSWORD=<smtp-pass or app-password>
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=<no-reply@yourdomain.com>
MAIL_FROM_NAME="${APP_NAME:-Task Manager}"
```

Optional integrations used by this project (enable only what you use):

```
# WhatsApp (Meta)
WHATSAPP_PROVIDER=meta
WHATSAPP_USE_TEMPLATES=true
META_WHATSAPP_TOKEN=<token>
META_WHATSAPP_PHONE_ID=<phone_id>
META_WHATSAPP_BUSINESS_ID=<business_id>
META_WHATSAPP_VERSION=v21.0

# Twilio (if you use SMS/WhatsApp via Twilio)
TWILIO_ACCOUNT_SID=<sid>
TWILIO_AUTH_TOKEN=<auth_token>
TWILIO_WHATSAPP_FROM=<whatsapp:+1234567890>
```

File storage (defaults to local/public). If you use S3 or similar, set:

```
FILESYSTEM_DISK=s3
AWS_ACCESS_KEY_ID=<key>
AWS_SECRET_ACCESS_KEY=<secret>
AWS_DEFAULT_REGION=<region>
AWS_BUCKET=<bucket>
AWS_URL=
```

## How to set variables (step-by-step)
1) Open Railway → Your Project.
2) Click Variables (left sidebar) → New Variable.
3) Add the Minimal variables above. Paste carefully; values are case-sensitive.
4) If you provisioned a Railway Database/Redis, click the service → Connect → copy its environment variables into your web service (or use “Reference” if available).
5) Redeploy: Service → Deployments → Redeploy → Full Rebuild / Clear Cache.

## Using the CLI (optional)
If you use the Railway CLI and are logged in and linked to this project:

```
# Core
railway variables set APP_ENV=production APP_DEBUG=false APP_URL=https://<your-url>

# MySQL example
railway variables set DB_CONNECTION=mysql DB_HOST=<host> DB_PORT=3306 DB_DATABASE=<db> DB_USERNAME=<user> DB_PASSWORD=<pass>

# Redis
railway variables set CACHE_DRIVER=redis QUEUE_CONNECTION=redis SESSION_DRIVER=redis \
  REDIS_HOST=<host> REDIS_PORT=6379 REDIS_PASSWORD=<pass>
```

## Common pitfalls and fixes
- 500 “No application encryption key specified”: set APP_KEY or trigger a full rebuild so the build step runs `key:generate`. You can also run a one-off shell: `php artisan key:generate --force`.
- SQLSTATE[HY000] connection refused: check DB_HOST/PORT; for Railway-managed DBs, use the exact host/port from the Database plugin. Ensure the service can reach the DB (same project or public access as configured).
- 419 Page Expired (CSRF): ensure APP_URL is the exact https URL you visit.
- Mail not sending: verify MAIL_* values and that the provider allows the from address. Use an app password for Gmail/Google Workspace.
- Queues stuck: make sure you run a worker service: start command `php artisan queue:work --tries=1 --timeout=90` and that Redis vars are set.

## Related docs in this repo
- WHATSAPP_QUICK_START.md
- TWILIO_SETUP_CHECKLIST.md
- NOTIFICATION_EMAIL_SETUP.md
- FIXED_EMAIL_ISSUE.md
