Local Development and Testing Guide

This project is a Laravel 11 app located in the task-scheduling-system/ subfolder. The steps below help you run it locally with SQLite, Vite, and a background queue worker so you can test login, dashboard, task assignment, and invite flows.

Prerequisites
- PHP 8.2+
- Composer
- Node.js 18+ (recommended Node 20)
- npm (or yarn/pnpm) — docs use npm

1) Clone and enter the app directory
```
cd task-scheduling-system
```

2) Install dependencies
```
composer install
npm install --legacy-peer-deps --no-audit --no-fund
```

3) Environment setup
- Copy the example env file:
```
cp .env.example .env
```
- Recommended local tweaks in .env:
  - APP_ENV=local
  - APP_DEBUG=true
  - APP_URL=http://127.0.0.1:8004
  - DB_CONNECTION=sqlite (already set)
  - Option A (simple): set MAIL_MAILER=log to capture emails in storage/logs/laravel.log
  - Option B (SMTP): keep your SMTP settings if you want to actually send emails
  - Optional diagnostics (enables /queue/health endpoint):
    DIAG_TOKEN=some-secret-string

Notes
- SQLite DB path: By default Laravel uses database/database.sqlite. This repo auto-creates the file at runtime if missing, but you can create it yourself too:
```
touch database/database.sqlite
```

4) Build front-end assets (first time)
```
npm run build
```

5) Run database migrations and seed the Super Admin
```
php artisan migrate --force
php artisan db:seed
```

Seeder credentials (default, configurable via .env):
- SUPERADMIN_EMAIL=superadmin@taskmanager.com
- SUPERADMIN_PASSWORD=password123

6) Start everything for local development
There is a single command that runs PHP server, Vite dev server, and the queue worker concurrently:
```
npm run dev:full
```
This runs:
- php artisan serve on http://127.0.0.1:8004
- vite (HMR for assets)
- php artisan queue:work (processes queued notifications)

Alternatively, run each in its own terminal:
```
# Terminal 1
php artisan serve --host=127.0.0.1 --port=8004

# Terminal 2
npm run dev

# Terminal 3
php artisan queue:work --tries=3 --timeout=120 --sleep=1 --backoff=3
```

7) Login and verify UI
- Open http://127.0.0.1:8004
- Login using the seeded Super Admin credentials from step 5.
- You should see your name and a Logout button in the top-right of the dashboard.

8) Verify queues (optional)
- Ensure you set DIAG_TOKEN in .env (e.g., DIAG_TOKEN=dev-secret) and restart the dev servers.
- Visit:
```
http://127.0.0.1:8004/queue/health?token=dev-secret
```
You’ll see the queue connection, job table status, counts of pending/failed jobs, and a ping will be dispatched. With the worker running, last_ping should update.

9) Testing emails locally
- If MAIL_MAILER=log, check storage/logs/laravel.log to see email contents.
- If using SMTP, ensure your provider allows SMTP from localhost and that credentials are correct.

10) Common issues & tips
- 419/CSRF after login: ensure you are using http://127.0.0.1:8004 (not localhost mismatches) and APP_URL matches that URL.
- Storage links for file uploads (if needed):
```
php artisan storage:link || true
```
- If you edited .env, restart dev processes (Ctrl+C and rerun npm run dev:full) so config picks up changes.

That’s it! You can now create tasks, assign them, send invites, and watch the queue worker process jobs locally.
