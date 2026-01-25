# Task Scheduler Setup Guide

## Overview
This guide explains how to set up automatic task reminders and overdue task detection for your Task Management System.

---

## Features Implemented

### 1. Automatic Overdue Task Detection
- **Frequency**: Every 5 minutes
- **What it does**:
  - Finds tasks past their scheduled time that are not completed
  - Updates their status to "overdue"
  - Sends notifications to both employee and admin via Email and WhatsApp

### 2. 1-Hour Task Reminders
- **Frequency**: Every 15 minutes
- **What it does**:
  - Finds tasks scheduled within the next hour
  - Sends reminder notifications to both employee and admin
  - Prevents duplicate reminders on the same day

---

## Deployment Steps

### Step 1: Run Database Migration

```bash
cd /home/kc/PhpstormProjects/smartwork/task-scheduling-system
php artisan migrate
```

This adds the `last_reminded_at` column to the tasks table.

### Step 2: Test the Commands Manually

Before setting up the scheduler, test each command:

**Test Overdue Task Detection:**
```bash
php artisan tasks:check-overdue
```

**Test Task Reminders:**
```bash
php artisan tasks:send-reminders
```

You should see output showing how many tasks were processed.

### Step 3: Set Up Laravel Scheduler

The scheduler is already configured in `app/Console/Kernel.php`. You need to add a single cron entry to your server.

#### For Production (Railway, Heroku, VPS, etc.)

Add this to your crontab:

```bash
* * * * * cd /path/to/your/project && php artisan schedule:run >> /dev/null 2>&1
```

**To edit crontab:**
```bash
crontab -e
```

Then paste the cron entry above (replace `/path/to/your/project` with your actual path).

#### For Railway Deployment

Railway doesn't support cron directly. You have two options:

**Option A: Use an external cron service**
- Use a service like **cron-job.org** or **EasyCron**
- Set it to hit an endpoint every minute: `https://your-app.railway.app/cron`
- Create a route to trigger the scheduler

**Option B: Use a worker process**
Create a `Procfile` in your project root:
```
web: vendor/bin/heroku-php-apache2 public/
worker: php artisan schedule:work
```

Then enable the worker in Railway dashboard.

### Step 4: Verify Scheduler is Running

Check Laravel logs:
```bash
tail -f storage/logs/laravel.log
```

You should see entries every 5-15 minutes when tasks are checked.

---

## Schedule Configuration

Current schedule (defined in `app/Console/Kernel.php`):

```php
// Check for overdue tasks every 5 minutes
$schedule->command('tasks:check-overdue')
    ->everyFiveMinutes()
    ->withoutOverlapping();

// Send 1-hour reminders every 15 minutes
$schedule->command('tasks:send-reminders')
    ->everyFifteenMinutes()
    ->withoutOverlapping();
```

### Customizing Frequency

Edit `app/Console/Kernel.php` to change frequencies:

**More frequent checks:**
```php
->everyMinute()      // Every minute
->everyTwoMinutes()  // Every 2 minutes
```

**Less frequent checks:**
```php
->everyTenMinutes()   // Every 10 minutes
->everyThirtyMinutes() // Every 30 minutes
->hourly()            // Every hour
```

---

## Notification Channels

### Email Notifications
Both features send email notifications automatically if configured in `.env`:

```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your-email@gmail.com
MAIL_PASSWORD=your-app-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@yourapp.com
MAIL_FROM_NAME="Task Management System"
```

### WhatsApp Notifications
Requires phone number in user profile and WhatsApp configuration:

```env
WHATSAPP_API_TOKEN=your-token
WHATSAPP_PHONE_NUMBER_ID=your-phone-id
```

---

## Testing

### Test Overdue Detection

1. Create a task scheduled in the past
2. Run: `php artisan tasks:check-overdue`
3. Check:
   - Task status changed to "overdue"
   - Email sent to employee and admin
   - WhatsApp message sent (if configured)

### Test 1-Hour Reminder

1. Create a task scheduled 30 minutes from now
2. Run: `php artisan tasks:send-reminders`
3. Check:
   - Reminder email sent to employee and admin
   - WhatsApp reminder sent (if configured)
   - `last_reminded_at` timestamp updated on task

---

## Troubleshooting

### Scheduler Not Running

**Problem**: Commands not executing automatically

**Solutions**:
1. Verify cron entry is correct
2. Check cron is running: `service cron status`
3. Test manually: `php artisan schedule:run`
4. Check permissions on project directory
5. View cron logs: `grep CRON /var/log/syslog`

### No Notifications Sent

**Problem**: Tasks detected but no emails/WhatsApp

**Solutions**:
1. Check queue is running: `php artisan queue:work`
2. Verify mail configuration in `.env`
3. Check Laravel logs: `storage/logs/laravel.log`
4. Test mail: `php artisan tinker` then `Mail::raw('Test', fn($m) => $m->to('test@test.com'))`
5. For WhatsApp, verify API credentials

### Duplicate Reminders

**Problem**: Multiple reminders for same task

**Solution**: The `last_reminded_at` field prevents this. If issues persist:
1. Check database has the new column: `php artisan migrate:status`
2. Verify `withoutOverlapping()` in Kernel.php
3. Clear cache: `php artisan cache:clear`

### Tasks Not Marked Overdue

**Problem**: Overdue tasks still showing as "pending"

**Solutions**:
1. Verify scheduler is running
2. Run manually: `php artisan tasks:check-overdue`
3. Check task scheduled time is truly in the past
4. Review Laravel logs for errors

---

## Manual Execution

### Run Scheduler Once
```bash
php artisan schedule:run
```

### Run Scheduler Continuously (Development)
```bash
php artisan schedule:work
```

This runs the scheduler every minute without needing cron.

### Check Scheduled Tasks
```bash
php artisan schedule:list
```

---

## Production Checklist

Before deploying to production:

- [ ] Database migration completed
- [ ] Cron entry added to server
- [ ] Mail configuration tested
- [ ] WhatsApp configuration tested (if using)
- [ ] Queue worker running (for notifications)
- [ ] Commands tested manually
- [ ] Logs monitored for errors
- [ ] Scheduler verified with `schedule:list`

---

## Queue Configuration

Both features use queued notifications for better performance.

### Start Queue Worker

**Development:**
```bash
php artisan queue:work
```

**Production (using Supervisor):**

Create `/etc/supervisor/conf.d/laravel-worker.conf`:
```ini
[program:laravel-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /path/to/your/project/artisan queue:work --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/path/to/your/project/storage/logs/worker.log
stopwaitsecs=3600
```

Then:
```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start laravel-worker:*
```

### For Railway
Add to `Procfile`:
```
web: vendor/bin/heroku-php-apache2 public/
worker: php artisan queue:work --verbose --tries=3 --timeout=90
scheduler: php artisan schedule:work
```

---

## Monitoring

### View Scheduler Output
```bash
tail -f storage/logs/laravel.log | grep -i "task"
```

### Check Task Status
```bash
php artisan tinker
>>> Task::where('status', 'overdue')->count()
>>> Task::whereNotNull('last_reminded_at')->count()
```

### Monitor Queue
```bash
php artisan queue:monitor
```

---

## Advanced Configuration

### Custom Reminder Time

To change reminder from 1 hour to 2 hours, edit:
`app/Console/Commands/SendTaskRemindersCommand.php`

Change line:
```php
$oneHourFromNow = $now->copy()->addHour();
```

To:
```php
$twoHoursFromNow = $now->copy()->addHours(2);
```

And update the query:
```php
$upcomingTasks = Task::whereBetween('scheduled_at', [$now, $twoHoursFromNow])
```

### Multiple Reminder Times

To send reminders at both 1 hour and 24 hours before:

1. Create new command: `php artisan make:command SendDayBeforeReminders`
2. Copy logic from `SendTaskRemindersCommand`
3. Change time window to 24 hours
4. Add to Kernel.php scheduler

---

## Files Created/Modified

### New Files:
- `app/Console/Kernel.php` - Scheduler configuration
- `app/Console/Commands/CheckOverdueTasksCommand.php` - Overdue detection
- `app/Console/Commands/SendTaskRemindersCommand.php` - Reminder sender
- `app/Notifications/TaskReminder.php` - Reminder notification
- `database/migrations/*_add_last_reminded_at_to_tasks_table.php` - Database schema

### Modified Files:
- `app/Models/Task.php` - Added `last_reminded_at` to fillable and casts
- `app/Jobs/CheckOverdueTasks.php` - Already existed, now used by command

---

## Support

For issues or questions:
1. Check Laravel logs: `storage/logs/laravel.log`
2. Review cron logs: `/var/log/syslog` (Linux)
3. Test commands manually first
4. Verify environment variables
5. Check queue workers are running

---

**Version**: 1.0
**Last Updated**: January 2026
