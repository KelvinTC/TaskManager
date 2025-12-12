# Different Email Addresses for Different Notifications

## Overview

The Task Manager system now supports sending different types of notifications from different email addresses. This helps recipients quickly identify the type of message they're receiving.

## Email Address Configuration

### Current Setup (in `.env`)

```env
# Main sender (fallback)
MAIL_FROM_ADDRESS="tadiwaroyalty@gmail.com"
MAIL_FROM_NAME="Task Manager"

# Invitation emails
MAIL_INVITE_ADDRESS="invites@taskmanager.com"
MAIL_INVITE_NAME="Task Manager Invitations"

# Task-related emails
MAIL_TASK_ADDRESS="tasks@taskmanager.com"
MAIL_TASK_NAME="Task Manager Tasks"

# General notifications
MAIL_NOTIFICATION_ADDRESS="notifications@taskmanager.com"
MAIL_NOTIFICATION_NAME="Task Manager Notifications"
```

## Notification Types and Email Addresses

| Notification Type | Sender Address | When Sent |
|------------------|----------------|-----------|
| **UserInvited** | `invites@taskmanager.com` | When a new user is invited to join |
| **TaskAssigned** | `tasks@taskmanager.com` | When a task is assigned to a user |
| **TaskRescheduled** | `tasks@taskmanager.com` | When a task schedule is changed |
| **TaskStatusUpdated** | `notifications@taskmanager.com` | When task status changes |
| **TaskOverdue** | `notifications@taskmanager.com` | When a task becomes overdue |

## Important Gmail Limitation

**Note:** Gmail SMTP does NOT support sending from arbitrary email addresses. Even though you configure different sender addresses in `.env`, all emails will still appear to come from your Gmail account (`tadiwaroyalty@gmail.com`).

However, the **sender name** will be different:
- Invitations: "Task Manager Invitations"
- Tasks: "Task Manager Tasks"
- Notifications: "Task Manager Notifications"

## How to Use Different Email Addresses (Production)

To actually send from different email addresses, you have these options:

### Option 1: Gmail Aliases (Free, Limited)
1. Go to Gmail Settings → Accounts → "Send mail as"
2. Add alias addresses (invites@, tasks@, etc.)
3. Google will verify ownership of these addresses
4. Update `.env` with verified addresses

### Option 2: Professional Email Service (Recommended)

Use a service that supports multiple sender addresses:

**SendGrid** (100 emails/day free):
```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.sendgrid.net
MAIL_PORT=587
MAIL_USERNAME=apikey
MAIL_PASSWORD=your_sendgrid_api_key
MAIL_ENCRYPTION=tls
```

**Mailgun** (5,000 emails/month free):
```env
MAIL_MAILER=mailgun
MAILGUN_DOMAIN=mg.yourdomain.com
MAILGUN_SECRET=your_mailgun_api_key
```

**Amazon SES** (Very cheap, $0.10/1000 emails):
```env
MAIL_MAILER=ses
AWS_ACCESS_KEY_ID=your_aws_key
AWS_SECRET_ACCESS_KEY=your_aws_secret
AWS_DEFAULT_REGION=us-east-1
```

### Option 3: Custom Domain Email

Set up email addresses on your own domain:
1. Get a domain (e.g., taskmanager.com)
2. Set up email hosting (Google Workspace, Microsoft 365, etc.)
3. Create actual email accounts:
   - invites@taskmanager.com
   - tasks@taskmanager.com
   - notifications@taskmanager.com
4. Use SMTP credentials for each

## Testing the System

### Test Invitation Email
```bash
# Create a test invitation from the UI or via tinker:
php artisan tinker
```

Then run:
```php
$user = User::find(1); // The inviter (must be admin or super_admin)
$invitation = new \App\Models\InvitedUser([
    'email' => 'test@example.com',
    'role' => 'employee',
    'invited_by' => $user->id
]);
$invitation->save();

// Send the notification
Notification::route('mail', 'test@example.com')
    ->notify(new \App\Notifications\UserInvited($invitation, $user, 'employee'));
```

### Test Task Notification
```bash
php artisan tinker
```

Then:
```php
$user = User::find(2); // The employee
$user->preferred_channel = 'email';
$user->save();

$task = Task::find(1); // Any task
$user->notify(new \App\Notifications\TaskAssigned($task));
```

Check the email inbox to see the different sender names!

## How It Works

Each notification class has a custom `from()` configuration:

```php
public function toMail($notifiable)
{
    return (new MailMessage)
        ->from(
            config('mail.task.address', config('mail.from.address')),
            config('mail.task.name', config('mail.from.name'))
        )
        ->subject('...')
        ->line('...');
}
```

The fallback values ensure that if custom addresses aren't configured, it uses the default `MAIL_FROM_ADDRESS`.

## Customization

To add new notification types with custom senders:

1. **Add to `.env`:**
```env
MAIL_REMINDER_ADDRESS="reminders@taskmanager.com"
MAIL_REMINDER_NAME="Task Manager Reminders"
```

2. **Add to `config/mail.php`:**
```php
'reminder' => [
    'address' => env('MAIL_REMINDER_ADDRESS', env('MAIL_FROM_ADDRESS')),
    'name' => env('MAIL_REMINDER_NAME', env('MAIL_FROM_NAME')),
],
```

3. **Use in notification:**
```php
->from(
    config('mail.reminder.address', config('mail.from.address')),
    config('mail.reminder.name', config('mail.from.name'))
)
```

## Troubleshooting

**Q: Emails still show as coming from my Gmail?**
A: This is expected with Gmail SMTP. Only the sender name changes, not the address.

**Q: How can I use different actual addresses?**
A: Switch to a professional email service (SendGrid, Mailgun, SES) or set up custom domain email.

**Q: Do I need to clear cache after changing .env?**
A: Yes! Run: `php artisan config:clear`

**Q: Can I test without setting up new email addresses?**
A: Yes! The system falls back to `MAIL_FROM_ADDRESS` if custom addresses aren't set up.

---

**Current Status:**
✅ Custom sender names configured
✅ All notifications updated
✅ Invitation emails integrated
⚠️ Using Gmail (sender address limitation)
💡 Consider professional email service for production
