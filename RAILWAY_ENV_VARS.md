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

---

## How do I migrate and seed MySQL on Railway?

If you’ve switched this app to use a MySQL database on Railway, here are the supported ways to run migrations and seeders.

Prerequisites:
- In Railway → Variables for your web service, set the DB variables to your MySQL instance:
  - DB_CONNECTION=mysql
  - DB_HOST, DB_PORT (3306), DB_DATABASE, DB_USERNAME, DB_PASSWORD
- Redeploy after changing variables so the app sees them.

Recommended: This repository is configured to run migrations and seeders automatically after each deploy. We updated railway.toml to execute:

```
php artisan migrate --force --seed
```

That means on every successful deployment, pending migrations run and DatabaseSeeder executes. Ensure your seeders are idempotent (safe to run multiple times) or use guards (e.g., firstOrCreate) to avoid duplicates.

Manual options (any time):

1) From Railway Web UI Shell
- Railway → Your Project → Services → web → Shell → Start shell.
- Run either separately or combined:
  - php artisan migrate --force
  - php artisan db:seed --force
  - Or in one go: php artisan migrate --force --seed
- To run only a specific seeder class:
  - php artisan db:seed --force --class=YourSeederClass

2) Using Railway CLI from your local terminal
- Link to your project: railway link
- Run artisan inside the remote service context:
  - railway run php artisan migrate --force --seed
  - Or just seeding: railway run php artisan db:seed --force --class=YourSeederClass

3) Running locally but targeting the Railway MySQL
- Copy the DB_* credentials from Railway → Database → Connect.
- Export them in your local shell for a one-off run (example):
  - export DB_CONNECTION=mysql
  - export DB_HOST=your-host
  - export DB_PORT=3306
  - export DB_DATABASE=your-db
  - export DB_USERNAME=your-user
  - export DB_PASSWORD=your-pass
  - php artisan migrate --force --seed

Verifying status
- Check which migrations ran: php artisan migrate:status
- Confirm seeded data using tinker: php artisan tinker → use App\Models\User; User::count();
- Connect with mysql client (from shell): mysql -h "$DB_HOST" -P "$DB_PORT" -u"$DB_USERNAME" -p"$DB_PASSWORD" "$DB_DATABASE"

Troubleshooting
- SQLSTATE[HY000] Connection refused: Verify DB_HOST/PORT/USER/PASS and that your web service and DB are in the same Railway project or accessible publicly (as configured).
- “No application encryption key specified”: ensure APP_KEY is set; redeploy to trigger key generation or run php artisan key:generate --force.
- Duplicate seed rows: make seeders idempotent (firstOrCreate, upsert, or check existence before insert).
- SSL/Mode issues: Some managed MySQL instances require SSL. If using external tools, enable SSL. Review SQL modes if strict mode causes migration errors.

Note: The sample .env in this repo defaults to SQLite for local/demo. Make sure the deployed service overrides DB_CONNECTION and related variables to MySQL.

## How do I check the database on Railway?

Use any of the following approaches depending on what database you provisioned (MySQL, PostgreSQL, Redis) and how your service is configured.

Important: In this repository, the sample .env is set to SQLite for local/demo use:

```
DB_CONNECTION=sqlite
DB_DATABASE=/app/database/database.sqlite
```

If your Railway service actually uses MySQL or Postgres (recommended for production), make sure you’ve set the DB_* variables accordingly in Railway → Variables as shown earlier in this doc. The steps below cover SQLite, MySQL, and Postgres.

### 1) Railway Web UI (fastest for managed DBs)
- Open Railway → Your Project → Select your Database plugin (e.g., “PostgreSQL”, “MySQL”).
- Click the “Data” or “Connect” tab to:
  - View tables and run simple queries (Data tab availability depends on plugin version/UI).
  - Copy connection credentials (host, port, database, user, password, URL) for external tools.

### 2) One-off shell inside your Web service (artisan + CLI clients)
- Railway → Your Project → Services → web → Shell → Start shell.
- For Laravel checks:
  - php artisan migrate:status — shows which migrations ran.
  - php artisan tinker, then run a quick query, for example:
    - DB::select('select 1');
    - use App\Models\User; User::count();

- If using SQLite (as in the default .env):
  - sqlite3 /app/database/database.sqlite
  - Inside sqlite3: .tables to list tables, select * from users limit 5; to inspect data.

- If using Postgres:
  - Ensure the psql client is available (it is on most Railway images). Then:
    - psql "host=$DB_HOST port=$DB_PORT dbname=$DB_DATABASE user=$DB_USERNAME password=$DB_PASSWORD sslmode=require"

- If using MySQL:
  - mysql -h "$DB_HOST" -P "$DB_PORT" -u"$DB_USERNAME" -p"$DB_PASSWORD" "$DB_DATABASE"

Tip: You can echo envs to confirm what the service sees: printenv | grep -E 'DB_|APP_ENV|APP_URL'

### 3) Railway CLI from your local machine
Prereqs: Install Railway CLI and log in.

- Link your local directory to the Railway project (if not already):
  - railway link

- Spawn a database tunnel/connection:
  - For Postgres example:
    - railway connect postgres
  - For MySQL example:
    - railway connect mysql

- Or simply read the variables to connect with your own client:
  - railway variables | grep -E 'DB_|PG|MYSQL'

Use those values with your local client:
- Postgres (psql): psql "postgres://USER:PASSWORD@HOST:PORT/DBNAME?sslmode=require"
- MySQL (mysql): mysql -h HOST -P PORT -u USER -pPASSWORD DBNAME

### 4) Use a GUI client (TablePlus, Beekeeper, DBeaver, etc.)
- Copy credentials from Railway → Database → Connect.
- Paste into your client. For Postgres, set SSL Mode to “require” if needed.

### 5) Quick health checks from Laravel
- php artisan migrate:status — migrations should be “Yes”.
- php artisan migrate --pretend — shows SQL that would run without executing.
- php artisan tinker — run lightweight reads/writes using Eloquent.

### Common gotchas
- Wrong DB selected: Ensure DB_CONNECTION and related DB_* envs in the Railway service match your actual DB plugin.
- SQLite vs Managed DB: If you deployed with default SQLite settings, your data is stored in /app/database/database.sqlite inside the container. Consider switching to a managed DB for durability and easier inspection.
- Network/SSL errors: Use the exact host/port from Railway and set sslmode=require (Postgres) or enable SSL in your client if the plugin mandates it.
