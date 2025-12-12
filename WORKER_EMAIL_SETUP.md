# Worker Email Configuration Guide

## ✅ Workers CAN Receive Emails!

The email notification system is fully configured and working. Workers (employees) will receive emails when:
- Tasks are assigned to them
- Tasks are rescheduled
- Task status is updated
- Tasks become overdue

**BUT** they need to have their `preferred_channel` set to `email`.

---

## How Notification Channels Work

Each user has a `preferred_channel` field that determines how they receive notifications:

| Channel | Description | Where Notifications Go |
|---------|-------------|----------------------|
| `email` | Email notifications | User's email inbox |
| `sms` | SMS notifications | User's phone via SMS |
| `whatsapp` | WhatsApp notifications | User's WhatsApp |
| `in_app` | In-app only | Database notifications (bell icon) |

**Default:** New users are set to `in_app` by default, so they won't receive emails unless you change it.

---

## Check Current Worker Email Settings

### Method 1: Using the Worker Email Command (Easiest!)
```bash
# List all workers and their email settings
php artisan worker:email list
```

This shows a nice table with:
- Worker ID, Name, Email
- Current notification channel
- Whether email is enabled

### Method 2: Via Artisan Tinker
```bash
php artisan tinker
```

Then run:
```php
// See all employees and their notification preferences
User::where('role', 'employee')->get(['id', 'name', 'email', 'preferred_channel']);

// See only employees who will receive emails
User::where('role', 'employee')->where('preferred_channel', 'email')->get(['name', 'email']);
```

---

## Enable Email for Workers

### Option 1: Using the Worker Email Command (Easiest!)

**Enable email for a specific worker:**
```bash
php artisan worker:email enable 3  # Replace 3 with worker's ID
```

**Enable email for ALL workers:**
```bash
php artisan worker:email enable-all
```

**Disable email for a worker:**
```bash
php artisan worker:email disable 3  # Replace 3 with worker's ID
```

### Option 2: Update via Tinker
```bash
php artisan tinker
```

Then for a specific employee:
```php
$employee = User::find(3); // Replace with employee ID
$employee->preferred_channel = 'email';
$employee->save();
```

Or update ALL employees at once:
```php
User::where('role', 'employee')->update(['preferred_channel' => 'email']);
```

### Option 2: Update via UI (If you have user settings page)

1. Login as the employee
2. Go to Profile/Settings
3. Select "Email" as preferred notification channel
4. Save

### Option 3: Set Default for New Employees

Update the user factory or registration to default to email:

**In `database/factories/UserFactory.php`:**
```php
'preferred_channel' => 'email', // Change from 'in_app'
```

**Or in user registration/invitation:**
```php
User::create([
    // ... other fields
    'preferred_channel' => 'email',
]);
```

---

## Test Worker Email

I've created a test script for you:

```bash
php test_worker_email.php
```

This will:
1. Find an employee with email preference
2. Create a test task assigned to them
3. Send a task assignment email
4. Show you the email details

**Expected Output:**
```
✓ Email notification sent successfully!
To: worker@example.com
From: tasks@taskmanager.com (Task Manager Tasks)
Subject: New Task Assigned: Test Task
```

Then check the worker's email inbox!

---

## Current Test Setup

We've created a test employee for you:

```
Name: John Employee
Email: tadiwaroyalty@gmail.com (your Gmail)
Password: password123
Role: employee
Preferred Channel: email ✓
```

This employee is **configured to receive emails** when tasks are assigned.

---

## How to Add Real Workers with Email

### Method 1: Invite via Admin Panel

1. Login as Super Admin
2. Go to "User Management" or "Invite Users"
3. Enter employee email and role
4. They receive invitation email from `invites@taskmanager.com`
5. After registration, set their `preferred_channel` to `email`

### Method 2: Create Directly

```bash
php artisan tinker
```

```php
use App\Models\User;
use Illuminate\Support\Facades\Hash;

User::create([
    'name' => 'Alice Worker',
    'email' => 'alice@company.com',
    'password' => Hash::make('password123'),
    'role' => 'employee',
    'phone' => '+263771234567',
    'preferred_channel' => 'email', // ← Important!
]);
```

---

## Troubleshooting

### Problem: Workers not receiving emails

**Check 1: Is preferred_channel set to 'email'?**
```bash
php artisan tinker
User::find(EMPLOYEE_ID)->preferred_channel
```

If it's not 'email', update it:
```php
$user = User::find(EMPLOYEE_ID);
$user->preferred_channel = 'email';
$user->save();
```

**Check 2: Is email address valid?**
```bash
php artisan tinker
User::find(EMPLOYEE_ID)->email
```

**Check 3: Are emails being sent?**
```bash
# Check Laravel logs
tail -f storage/logs/laravel.log
```

**Check 4: Test email system**
```bash
php artisan email:test worker@example.com
```

### Problem: Email goes to spam

- Ask workers to check spam/junk folder
- Add sender to contacts: "Task Manager Tasks <tadiwaroyalty@gmail.com>"
- Mark email as "Not Spam"

### Problem: Gmail shows wrong sender

This is normal! Gmail doesn't allow sending from arbitrary addresses. See `NOTIFICATION_EMAIL_SETUP.md` for details on how to use professional email services for true multi-address sending.

---

## Email Notification Flow

1. **Admin assigns task** to employee
2. **System checks** employee's `preferred_channel`
3. **If channel is 'email':**
   - Creates TaskAssigned notification
   - Queues email job
   - Sends email via Gmail SMTP
   - Email shows sender as "Task Manager Tasks"
4. **If channel is 'in_app':**
   - Only creates database notification
   - No email sent
   - Shows in notification bell

---

## Quick Setup Checklist

For each worker to receive emails:

- [ ] Worker has valid email address
- [ ] Worker's `preferred_channel` is set to `email`
- [ ] SMTP credentials are configured in `.env`
- [ ] Config cache is cleared (`php artisan config:clear`)
- [ ] Queue worker is running (if using queues)

---

## Run Queue Worker (Important!)

Since notifications use `ShouldQueue`, you need a queue worker running:

```bash
# Start queue worker
php artisan queue:work

# Or for development (auto-restart on code changes)
php artisan queue:listen

# Check queue status
php artisan queue:failed
```

**Without a queue worker**, emails will be queued but not sent!

**Alternative:** Remove `implements ShouldQueue` from notification classes to send immediately (not recommended for production).

---

## Summary

✅ **Email system is fully configured**
✅ **Workers CAN receive emails**
✅ **Test employee created** (tadiwaroyalty@gmail.com with email preference)
✅ **All notification types configured** with custom sender names

⚠️ **Workers need `preferred_channel = 'email'`** to receive emails

💡 **Run `php test_worker_email.php`** to test the system

📧 **Check your Gmail** after running the test script!
