# Railway environment variables setup

This project is configured to deploy on Railway using the railway.toml at the repo root. Use this guide to set the minimum and recommended environment variables so the app boots correctly in production.

Important about APP_KEY
- APP_KEY does not expire. It’s a long‑term secret used by Laravel to encrypt/decrypt cookies, sessions (if encrypted), and data you encrypt via Crypt. Keep it secret and stable.
- Recommended: Set APP_KEY explicitly in Railway → Variables for your web and worker services. Do not commit it to git.
- Safety net: This repo includes a runtime safeguard that generates a temporary APP_KEY if one is missing. This is only to prevent boot‑time 500s; you should still set a real APP_KEY in Railway for consistency across replicas and deploys.

## Where to add variables in Railway
- Railway → Your Project → Variables
  - Project-level variables apply to all services unless overridden.
- Or per service: Railway → Service (web/worker) → Variables

Tip: Keep secrets (DB_PASSWORD, MAIL_PASSWORD, API tokens) only in Railway. Do not commit them to git.

### Should I edit the .env file to use Railway MySQL credentials?
- Short answer: No. Do not edit/commit the repo’s .env with your Railway MySQL credentials. Instead, set the DB_* variables in Railway → Variables.
- Why: This project’s deploy flow reads environment variables provided by Railway at runtime. During deployment we clear config/route caches first, so Laravel uses the live DB_* values from Railway before running migrations/seeders. Committing secrets to .env is insecure and can cause cache mismatches on deploys.

What to do instead (MySQL example)
- In Railway → Your Project → Service “web” → Variables, set:
  - DB_CONNECTION=mysql
  - DB_HOST=<db-host>
  - DB_PORT=3306
  - DB_DATABASE=<db-name>
  - DB_USERNAME=<db-user>
  - DB_PASSWORD=<db-pass>
- Redeploy the service (Full Rebuild if you recently changed variables).
- Verify:
  - Shell: php artisan migrate:status
  - Connect: mysql -h "$DB_HOST" -P "$DB_PORT" -u"$DB_USERNAME" -p"$DB_PASSWORD" "$DB_DATABASE"

Notes
- The sample .env in this repo defaults to SQLite for local/demo use. That’s fine locally, but production credentials should live in Railway Variables.
- For local development against Railway MySQL, you can export DB_* in your terminal session temporarily (do not commit them) and run artisan commands locally.
- See also:
  - “Minimal variables to go live” (below)
  - “How do I migrate and seed MySQL on Railway?”
  - “Does this auto-setup the database and tables on Railway?”

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

APP_KEY: Strongly recommended to set in Railway Variables. Generate locally with `php artisan key:generate --show` and paste the value. If unset, a temporary key will be generated at runtime by bootstrap/app.php, but that is intended only as a fallback and may invalidate sessions on each deploy.

## FAQ: Does APP_KEY expire?
Short answer: No. Laravel’s APP_KEY does not expire on its own. It’s a symmetric encryption key (32 bytes, base64‑encoded) used to protect encrypted cookies, certain session data (when SESSION_ENCRYPT=true), and anything you encrypt with Laravel’s Crypt facade. As long as the key remains secret and unchanged, your app will continue to work.

When should I rotate APP_KEY?
- If you suspect the key was exposed or leaked.
- If your organization mandates periodic key rotation by policy.
- If you are moving environments and want to isolate old data from new.

What happens if I change APP_KEY?
- Existing encrypted cookies become invalid. Users will be logged out and will need to sign in again.
- Encrypted session data (if SESSION_ENCRYPT=true) will be unreadable and effectively reset.
- Any data you manually encrypted and stored (e.g., in the database) with the old key will not decrypt with the new key. You must decrypt with the old key and re‑encrypt with the new one as part of your rotation plan.
- Database contents that are not encrypted (most tables by default) are unaffected.
- API tokens, passwords, and hashes are unaffected (they do not depend on APP_KEY).

Safe rotation procedure on Railway
1) Generate a new key locally (or in a secure environment):
   - php artisan key:generate --show
   - Copy the full value (including the base64: prefix).
2) In Railway → Your Project → Variables, update APP_KEY to the new value at the project level (applies to web and worker), or update per service if you prefer.
3) Redeploy with a Full Rebuild to ensure all replicas use the same new key.
4) Expect that existing browser sessions are invalidated (users may need to log in again).
5) If you store application data encrypted with Crypt, plan a maintenance window to re‑encrypt it:
   - Export/backup the encrypted data.
   - Temporarily run code that decrypts with the old key and re‑encrypts with the new key, or write a one‑off artisan command to do this.
   - Only after re‑encryption is complete should you remove access to the old key.

Zero/low‑downtime considerations
- To avoid user disruption, rotate during low‑traffic hours and announce that sessions will be reset.
- If you must keep existing encrypted-at-rest data readable during rotation, implement a temporary “dual key” reader that tries the new key first and falls back to the old key, then re‑writes with the new key on save. Remove fallback after migration completes. This requires custom code and careful testing.

Common misconceptions
- “APP_KEY expires every X days” → False. It never expires automatically.
- “APP_KEY rotates when I redeploy” → False if you set APP_KEY in Railway Variables. True if you rely on a runtime‑generated fallback, which is why you should set APP_KEY explicitly.
- “Changing APP_KEY will break my database” → Generally false. Only data you encrypted with Crypt will be affected; normal tables, password hashes, and tokens remain valid.

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
- Time zone warning during deploy: If you see `Unable to load '/usr/share/zoneinfo/tzdata.zi' as time zone. Skipping it.`, the base image is missing tzdata. This repo includes nixpacks.toml to install `tzdata` and sets `TZ=UTC` in railway.toml. Trigger a Full Rebuild to apply.
- Changed service port on Railway: Railway normally injects an environment variable named `PORT` that the app must bind to. This repo's start command respects `PORT` when present and now falls back to `8081` if it's not set. If you configured your service to expect port 8081, you're covered. If Railway injects a `PORT` (common), that value will still be used regardless of the fallback.
- Vite/Node build failures: Ensure the Node version is compatible with your Vite version. This project pins `NODE_VERSION=20` in railway.toml variables. Also note `npm ci` requires a package-lock.json; if you remove the lockfile, switch the build step to `npm install --legacy-peer-deps` or restore the lockfile.
- fatal: not a git repository: Some logging stacks add Monolog's GitProcessor which runs `git` commands to include branch/commit in logs. In containers (no .git directory), this prints `fatal: not a git repository`. This repo disables VCS processors by default via a logging tap (App\\Logging\\DisableGitProcessor). If you really need git info in logs, set `LOG_GIT_INFO=true` in Railway Variables. Otherwise, leave it unset/false to avoid the warning.

## How to run queues on Railway (emails & WhatsApp)

This app supports queued jobs for things like emails and WhatsApp notifications. In production you should run a dedicated worker alongside the web service.

What this repo already configures
- A separate worker service in railway.toml that continuously runs the queue worker:
  - Command: `php artisan queue:work --tries=3 --timeout=120 --sleep=1 --backoff=3`
- The web deploy command triggers `php artisan queue:restart` after each deployment so workers gracefully reload fresh code.
- Default queue driver set to database unless overridden (see config/queue.php and railway.toml variables).

Minimal setup on Railway
1) Create the worker service
   - When you push this repo, Railway will show the “web” service automatically.
   - Add another service from the same repo and select the path `task-scheduling-system` (or use the existing [services.worker] declared in railway.toml if your plan supports multi‑service). The worker’s start command is already defined in railway.toml.
2) Set Variables (project level or per service)
   - QUEUE_CONNECTION=database (default in this repo)
   - DB_* variables pointing to your production database (same ones the web uses)
   - MAIL_* for emails, and your WhatsApp provider variables (META_* or Twilio) as needed.
3) Deploy both services
   - Trigger a Full Rebuild so the image builds once and both services pick it up.

Database vs Redis
- Database (default): simplest — uses the same DB for the `jobs` and `failed_jobs` tables. Migrations for these tables are included.
- Redis (recommended for higher throughput): set vars and switch the driver.

Redis example (optional)
```
CACHE_DRIVER=redis
QUEUE_CONNECTION=redis
SESSION_DRIVER=redis
REDIS_HOST=<host>
REDIS_PORT=6379
REDIS_PASSWORD=<password-if-any>
```

Verification steps
- From the web or worker Shell in Railway:
  - php artisan queue:failed      # list failed jobs
  - php artisan queue:retry all   # retry if needed
  - php artisan queue:work --once # run one job immediately (manual test)
- Trigger a real job from the app (e.g., send a test email or WhatsApp) and watch Logs on the worker service. You should see the job processed.

Operational tips
- queue:restart is executed automatically after each deploy by the web service, so long‑running workers reload code safely.
- Increase `--tries` if transient provider errors are common; adjust `--timeout` if your jobs perform network calls that may take longer.
- For WhatsApp and email providers, confirm credentials/permissions and that your FROM/phone numbers are allowed.

Troubleshooting
- Jobs remain “pending” and never process: ensure the worker service is running and healthy; check that QUEUE_CONNECTION matches the configured backend and that DB/Redis vars are correct on the worker.
- SQLSTATE connection errors on worker: copy the same DB_* vars from web into worker, or define them at project level.
- Mail/WhatsApp send failures: inspect the worker logs for provider error messages (e.g., 401/403). Verify tokens and sender settings.

### Railway predeploy/start command overrides (very important)
If you copied commands from a VPS guide (e.g., `git pull`, `sudo systemctl`, `nginx reload`) into Railway’s “Predeploy” or “Start” fields, your deploy will fail. Containers on Railway:

- Do not have `git` history (no `.git` folder), so `git pull` fails.
- Do not have `sudo` or `systemd`, so `sudo systemctl ...` fails.
- Do not use your own nginx/php-fpm service scripts; Nixpacks provisions and starts nginx + PHP-FPM for you.

What to do instead
- Remove any custom Predeploy/Start commands in the Railway UI for this service.
- Rely on the repository’s railway.toml:
  - Build (Nixpacks) handles PHP/Node and Vite.
  - Deploy command (defined in `[services.web.deploy].command`) runs:
    - `php artisan config:clear && php artisan route:clear && php artisan migrate --force --seed && php artisan config:cache && php artisan route:cache`
- The app is served by nginx + PHP-FPM automatically. Do not set a custom `startCommand` unless there is a specific reason.

Checklist to reset to safe defaults
1. Railway → Service “web” → Settings:
   - Clear any Predeploy command.
   - Clear any custom Start command.
2. Railway → Variables:
   - Set `APP_KEY` to a valid `base64:` key.
   - Set DB_* or ensure MYSQL_* vars exist (from the Database plugin).
3. Redeploy → Full Rebuild.

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

---

## Does this auto-setup the database and tables on Railway?

Short answer: Yes — on each deploy the app will automatically run Laravel migrations and seeders to create/update your tables and insert initial data, as long as your Railway environment variables point to a working database.

What happens automatically on deploy
- The deploy command in railway.toml runs:
  - php artisan config:clear && php artisan route:clear
  - php artisan migrate --force --seed
  - php artisan config:cache && php artisan route:cache
- This ensures the DB_* variables from Railway are used, then runs migrations and DatabaseSeeder.

Prerequisites (required for MySQL/Postgres)
- Set these in Railway → Service “web” → Variables:
  - DB_CONNECTION=mysql (or pgsql)
  - DB_HOST, DB_PORT (3306 for MySQL, 5432 for Postgres)
  - DB_DATABASE, DB_USERNAME, DB_PASSWORD
- The database itself must already exist (Railway’s managed DB plugins create it for you; external providers must be pre-created). Migrations create tables and indexes but do not create the database schema on the server.

What about SQLite?
- The sample .env in this repo uses SQLite for local/demo use:
  - DB_CONNECTION=sqlite
  - DB_DATABASE=/app/database/database.sqlite
- On Railway, if you leave it as SQLite, migrations will target the file at /app/database/database.sqlite inside the container. This works for demos but is not recommended for production persistence.

Verifying it worked
- Railway → Service → web → Shell:
  - php artisan migrate:status
  - php artisan tinker → use App\Models\User; User::count();
- Or connect directly:
  - MySQL: mysql -h "$DB_HOST" -P "$DB_PORT" -u"$DB_USERNAME" -p"$DB_PASSWORD" "$DB_DATABASE"
  - Postgres: psql "host=$DB_HOST port=$DB_PORT dbname=$DB_DATABASE user=$DB_USERNAME password=$DB_PASSWORD sslmode=require"

If tables still don’t appear
- Redeploy the service (Full Rebuild if you recently changed variables).
- Double-check that your DB_* variables are set on the web service (or project level) and correct for your DB.
- Review deploy logs for migration errors.
- Ensure seeders are idempotent to avoid duplicates, since seeding runs after each deploy.
