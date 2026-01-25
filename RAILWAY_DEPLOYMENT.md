# Railway Deployment Guide - Task Scheduler & Queue Workers

## Overview
This guide covers deploying the Task Management System to Railway with automatic task reminders and overdue detection.

---

## Procfile Configuration

The `Procfile` has been updated with three processes:

```
web: bash start.sh
worker: php artisan queue:work --verbose --tries=3 --timeout=90
scheduler: php artisan schedule:work
```

### Process Descriptions:

1. **web**: Main application (Laravel HTTP server)
2. **worker**: Queue worker for processing notifications (Email & WhatsApp)
3. **scheduler**: Laravel scheduler for automatic task checking

---

## Deployment Steps

### Step 1: Push Your Code to Railway

```bash
# Add all changes
git add .

# Commit changes
git commit -m "Add task scheduler and reminders for Railway"

# Push to Railway (or your git repository)
git push
```

### Step 2: Enable Worker and Scheduler Services

**Important**: Railway only runs the `web` process by default. You must enable the other processes manually.

#### In Railway Dashboard:

1. Go to your project in Railway
2. Click on your service
3. Go to **"Settings"** tab
4. Scroll to **"Deploy"** section
5. Look for **"Procfile Processes"** or **"Service Replicas"**
6. You should see three options:
   - ✅ **web** (enabled by default)
   - ☐ **worker**
   - ☐ **scheduler**

7. **Enable both `worker` and `scheduler`** by checking their boxes
8. Click **"Save"** or **"Deploy"**

**Alternative Method (if checkboxes not available):**

Railway will deploy all processes in the Procfile automatically on newer versions. If you don't see checkboxes, the processes should start automatically.

### Step 3: Verify Deployment

#### Check Logs

In Railway Dashboard:
1. Click on your service
2. Go to **"Deployments"** tab
3. Click on the latest deployment
4. Check the logs for each process

**What to look for:**

**Web Process:**
```
Server started on port 8080
```

**Worker Process:**
```
Processing jobs from 'default' queue
```

**Scheduler Process:**
```
Running scheduled commands every minute
```

#### Monitor Scheduler Activity

After deployment, within 5-15 minutes you should see in the scheduler logs:
```
Checking for overdue tasks...
Checking for tasks due within 1 hour...
```

---

## Environment Variables

Make sure these are set in Railway:

### Required Variables:

```env
APP_KEY=base64:your-generated-key
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-app.railway.app

DB_CONNECTION=pgsql
DB_HOST=your-db-host
DB_PORT=5432
DB_DATABASE=your-database
DB_USERNAME=your-username
DB_PASSWORD=your-password

# Queue Configuration
QUEUE_CONNECTION=database

# Mail Configuration
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your-email@gmail.com
MAIL_PASSWORD=your-app-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@yourapp.com
MAIL_FROM_NAME="Task Management System"

# WhatsApp (Optional)
WHATSAPP_API_TOKEN=your-whatsapp-token
WHATSAPP_PHONE_NUMBER_ID=your-phone-id

# Session
SESSION_DRIVER=database
SESSION_LIFETIME=120
```

### To Add/Edit Variables in Railway:

1. Go to your project
2. Click on your service
3. Go to **"Variables"** tab
4. Click **"+ New Variable"**
5. Add variable name and value
6. Click **"Add"**
7. Repeat for all variables
8. Railway will automatically redeploy

---

## Database Migration

### First Time Deployment:

Railway should run migrations automatically if you have this in your deployment script. If not:

1. Go to Railway Dashboard
2. Click your service
3. Go to **"Settings"** → **"Deploy"**
4. Under **"Custom Start Command"**, you can add migration commands

**Or manually via Railway CLI:**

```bash
# Install Railway CLI
npm i -g @railway/cli

# Login
railway login

# Link to your project
railway link

# Run migration
railway run php artisan migrate --force
```

---

## Testing the Scheduler

### Method 1: Check Logs

1. Go to Railway Dashboard
2. Click on **scheduler** process
3. View logs in real-time
4. You should see output every 5-15 minutes

### Method 2: Create Test Tasks

1. Log in to your deployed app
2. Create a task scheduled in the past (to test overdue)
3. Create a task scheduled 30 minutes from now (to test reminder)
4. Wait and check:
   - Email inbox for notifications
   - WhatsApp for messages
   - Database for status changes

### Method 3: Manual Command Execution

Using Railway CLI:

```bash
# Check overdue tasks
railway run php artisan tasks:check-overdue

# Send reminders
railway run php artisan tasks:send-reminders

# List scheduled tasks
railway run php artisan schedule:list
```

---

## Monitoring

### View Process Logs

**Scheduler Logs:**
```bash
railway logs --filter scheduler
```

**Worker Logs:**
```bash
railway logs --filter worker
```

**Web Logs:**
```bash
railway logs --filter web
```

### Check Process Status

In Railway Dashboard, each process shows:
- 🟢 **Running** - Process is active
- 🔴 **Crashed** - Process has errors
- ⏸️ **Stopped** - Process is disabled

---

## Troubleshooting

### Scheduler Not Running

**Problem**: No scheduled tasks executing

**Solutions:**

1. **Verify scheduler process is enabled**
   - Check Railway Dashboard → Processes
   - Ensure "scheduler" is checked/enabled

2. **Check scheduler logs**
   ```bash
   railway logs --filter scheduler
   ```
   - Should show "Running scheduled commands"

3. **Verify Procfile**
   - Ensure line exists: `scheduler: php artisan schedule:work`
   - No typos or extra spaces

4. **Redeploy**
   ```bash
   git commit --allow-empty -m "Trigger redeploy"
   git push
   ```

### Worker Not Processing Jobs

**Problem**: Notifications not being sent

**Solutions:**

1. **Check worker process is enabled**
   - Railway Dashboard → Processes
   - Enable "worker" if disabled

2. **Verify queue connection**
   - Set `QUEUE_CONNECTION=database` in Railway variables

3. **Check worker logs**
   ```bash
   railway logs --filter worker
   ```

4. **Verify database tables exist**
   ```bash
   railway run php artisan queue:table
   railway run php artisan migrate --force
   ```

### No Notifications Sent

**Problem**: Tasks detected but no emails/WhatsApp

**Solutions:**

1. **Check mail configuration**
   - Verify all `MAIL_*` variables in Railway
   - Test: `railway run php artisan tinker`
   - Then: `Mail::raw('Test', fn($m) => $m->to('test@test.com'))`

2. **For Gmail users**
   - Use App Password, not regular password
   - Enable "Less secure app access" (if needed)
   - Check Gmail settings

3. **Check WhatsApp configuration**
   - Verify `WHATSAPP_API_TOKEN` is set
   - Verify `WHATSAPP_PHONE_NUMBER_ID` is set
   - Check user has phone number in database

4. **Check queue is processing**
   ```bash
   railway logs --filter worker
   ```
   - Should show "Processing" messages

### Database Migration Errors

**Problem**: Migration not running on Railway

**Solutions:**

1. **Run manually**
   ```bash
   railway run php artisan migrate --force
   ```

2. **Check database connection**
   - Verify all `DB_*` variables in Railway
   - Test: `railway run php artisan db:show`

3. **Check migration files exist**
   ```bash
   railway run ls -la database/migrations
   ```

---

## Cost Considerations

### Railway Pricing

Railway charges based on:
- **Web process**: Always running (required)
- **Worker process**: Always running (handles notifications)
- **Scheduler process**: Always running (checks tasks)

**Total**: 3 processes running 24/7

### Optimization Options

If you want to reduce costs:

**Option 1: Combine Worker and Scheduler**

Create `combined-worker.sh`:
```bash
#!/bin/bash
php artisan schedule:work &
php artisan queue:work --verbose --tries=3 --timeout=90
```

Update Procfile:
```
web: bash start.sh
worker: bash combined-worker.sh
```

**Option 2: Use External Cron Service**

1. Remove scheduler process from Procfile
2. Create a route to trigger scheduler:
   ```php
   // routes/web.php
   Route::get('/cron', function() {
       Artisan::call('schedule:run');
       return 'OK';
   });
   ```
3. Use free service like **cron-job.org** to hit:
   - `https://your-app.railway.app/cron` every minute

**Note**: External cron is less reliable than Railway's scheduler process.

---

## Scaling

### Increase Worker Processes

If you have many notifications:

1. Railway Dashboard → Service → Settings
2. Adjust worker replicas/instances
3. Or update Procfile:
   ```
   worker: php artisan queue:work --verbose --tries=3 --timeout=90 --queue=default,notifications
   ```

### Monitor Queue Depth

```bash
railway run php artisan queue:monitor
```

If queue is backing up, increase worker instances.

---

## Maintenance

### View Scheduled Tasks

```bash
railway run php artisan schedule:list
```

Output:
```
0 */5 * * * tasks:check-overdue ........... Next Due: 2 minutes from now
0 */15 * * * tasks:send-reminders ......... Next Due: 8 minutes from now
```

### Clear Failed Jobs

```bash
railway run php artisan queue:flush
```

### Restart Processes

In Railway Dashboard:
1. Go to Deployments
2. Click "..." menu
3. Click "Restart"

Or redeploy:
```bash
git commit --allow-empty -m "Restart processes"
git push
```

---

## Verification Checklist

After deployment, verify:

- [ ] Web process running (app accessible)
- [ ] Worker process running (check logs)
- [ ] Scheduler process running (check logs)
- [ ] Database migrations applied
- [ ] Environment variables set
- [ ] Test task created
- [ ] Overdue detection working
- [ ] Reminders being sent
- [ ] Email notifications working
- [ ] WhatsApp notifications working (if configured)
- [ ] No errors in logs

---

## Support & Debugging

### View All Logs

```bash
railway logs --all
```

### Check Environment

```bash
railway run php artisan about
railway run php artisan config:show queue
railway run php artisan config:show mail
```

### Test Commands Manually

```bash
# From your local machine connected to Railway
railway run php artisan tasks:check-overdue
railway run php artisan tasks:send-reminders
```

### Database Inspection

```bash
railway run php artisan tinker
```

Then in tinker:
```php
Task::where('status', 'overdue')->count()
Task::whereNotNull('last_reminded_at')->get()
DB::table('jobs')->count()
```

---

## Quick Commands Reference

```bash
# Deploy
git push

# View logs
railway logs --filter scheduler
railway logs --filter worker

# Run commands
railway run php artisan tasks:check-overdue
railway run php artisan tasks:send-reminders

# Migrations
railway run php artisan migrate --force

# Clear cache
railway run php artisan cache:clear
railway run php artisan config:clear

# Restart
git commit --allow-empty -m "Restart"
git push
```

---

## Additional Resources

- **Railway Docs**: https://docs.railway.app
- **Laravel Scheduler**: https://laravel.com/docs/scheduling
- **Laravel Queues**: https://laravel.com/docs/queues

---

**Last Updated**: January 2026
**Version**: 1.0

For issues specific to Railway deployment, check Railway status page or Discord community.
