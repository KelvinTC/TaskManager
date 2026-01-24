# Railway Queue Worker Setup

This guide explains how the queue worker is configured for Railway deployment.

## How It Works

The application uses a custom startup script (`start.sh`) that:

1. Runs the initial setup (migrations, user creation, etc.)
2. Starts the Laravel queue worker in the background
3. Starts PHP-FPM and Nginx to serve the application

## Files Involved

### 1. `start.sh` - Main Startup Script
This script runs when Railway starts your container. It:
- Executes `.railway/setup.sh` for initial setup
- Starts `php artisan queue:work` in the background
- Starts PHP-FPM and Nginx

### 2. `railway.json` - Railway Configuration
Tells Railway to use `start.sh` as the startup command.

### 3. Queue Configuration
- **Queue Driver**: `database` (configured in `.env` via `QUEUE_CONNECTION=database`)
- **Jobs Table**: `jobs` (for pending jobs)
- **Failed Jobs Table**: `failed_jobs` (for failed jobs)

## Queue Worker Settings

The queue worker is started with these options:
```bash
php artisan queue:work --sleep=3 --tries=3 --max-time=3600 --daemon
```

**Parameters:**
- `--sleep=3`: Sleep 3 seconds when no jobs are available
- `--tries=3`: Retry failed jobs up to 3 times
- `--max-time=3600`: Restart worker after 1 hour (prevents memory leaks)
- `--daemon`: Run in daemon mode (background)

## What Gets Queued?

Currently, these notifications implement `ShouldQueue` (will be queued):
- `TaskOverdue`
- `TaskRescheduled`
- `TaskStatusUpdated`
- `UserInvited`

These notifications are sent **immediately** (not queued):
- `TaskAssigned` (removed ShouldQueue to fix serialization issues)

## Monitoring Queue on Railway

### Check Failed Jobs
```bash
railway run php artisan queue:failed
```

### Retry Failed Jobs
```bash
railway run php artisan queue:retry all
```

### Clear Failed Jobs
```bash
railway run php artisan queue:flush
```

## Alternative: Remove All Queuing (Simpler)

If you want to simplify deployment and send all notifications immediately, remove `implements ShouldQueue` from all notification classes:

- `app/Notifications/TaskOverdue.php`
- `app/Notifications/TaskRescheduled.php`
- `app/Notifications/TaskStatusUpdated.php`
- `app/Notifications/UserInvited.php`

**Pros:**
- Simpler deployment (no queue worker needed)
- No failed jobs to monitor
- Notifications sent immediately

**Cons:**
- Slower response times if notifications fail
- Could timeout on slow WhatsApp API responses

## Deployment Steps

1. **Commit all files:**
   ```bash
   git add start.sh railway.json Procfile
   git commit -m "Add queue worker for Railway deployment"
   git push
   ```

2. **Railway will automatically:**
   - Build your application
   - Run `start.sh`
   - Start the queue worker in the background
   - Start the web server

3. **Verify it's working:**
   - Check Railway logs to see "Starting queue worker..."
   - Create a task and check if WhatsApp notifications are sent
   - Check for any failed jobs in the Railway console

## Troubleshooting

### Queue worker not running
Check Railway logs for any errors during startup.

### Notifications not being sent
1. Check if jobs are in the `jobs` table
2. Check if jobs are in the `failed_jobs` table
3. Check Railway logs for WhatsApp API errors

### Queue worker crashes
The restart policy in `railway.json` will automatically restart the worker if it crashes.

## Production Recommendations

For production, consider:

1. **Use Redis instead of database queue:**
   - Add Redis service in Railway
   - Set `QUEUE_CONNECTION=redis`
   - Update `REDIS_HOST`, `REDIS_PASSWORD`, `REDIS_PORT` in Railway variables

2. **Add Horizon (Laravel queue monitoring):**
   ```bash
   composer require laravel/horizon
   php artisan horizon:install
   ```

3. **Use separate worker service:**
   - Create a second Railway service
   - Set start command to: `php artisan queue:work`
   - This separates web and worker processes

## Current Setup: Simple & Effective

The current setup runs the queue worker alongside the web server in a single Railway service. This is:
- ✅ Simple to deploy
- ✅ Cost-effective (one service)
- ✅ Works well for small to medium traffic
- ✅ Automatic restart on failure

For most use cases, this setup is sufficient!
