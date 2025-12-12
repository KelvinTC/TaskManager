# Email Setup Guide

## Current Configuration
The application is now configured to send real emails using SMTP.

## Option 1: Mailtrap (Recommended for Testing)
Mailtrap catches all emails sent from your app so you can test without sending to real email addresses.

### Setup Steps:
1. Go to https://mailtrap.io/
2. Sign up for a free account
3. Go to "Email Testing" > "Inboxes"
4. Click on your inbox
5. Under "SMTP Settings", select "Laravel 9+"
6. Copy the credentials and update your `.env` file:

```env
MAIL_USERNAME=your_mailtrap_username
MAIL_PASSWORD=your_mailtrap_password
```

### Benefits:
- ✅ Free forever
- ✅ See exactly what emails look like
- ✅ No risk of sending test emails to real users
- ✅ Perfect for development

---

## Option 2: Gmail (For Real Emails)
Use Gmail to send actual emails to real addresses.

### Setup Steps:
1. Enable 2-Factor Authentication on your Google account
2. Generate an App Password:
   - Go to https://myaccount.google.com/apppasswords
   - Select "Mail" and your device
   - Copy the 16-character password

3. Update `.env`:
```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your-email@gmail.com
MAIL_PASSWORD=your-16-char-app-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=your-email@gmail.com
MAIL_FROM_NAME="Task Manager"
```

### Limitations:
- ⚠️ Gmail has a daily sending limit (500 emails/day)
- ⚠️ Not recommended for production

---

## Option 3: SendGrid (For Production)
Professional email service for production applications.

### Setup Steps:
1. Sign up at https://sendgrid.com/ (Free tier: 100 emails/day)
2. Create an API key
3. Install SendGrid package:
```bash
composer require sendgrid/sendgrid
```

4. Update `.env`:
```env
MAIL_MAILER=sendgrid
MAIL_HOST=smtp.sendgrid.net
MAIL_PORT=587
MAIL_USERNAME=apikey
MAIL_PASSWORD=your-sendgrid-api-key
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@yourdomain.com
MAIL_FROM_NAME="Task Manager"
```

---

## Option 4: Keep Logging (Current Fallback)
If you don't add credentials, emails will fail silently or you can switch back to logging:

```env
MAIL_MAILER=log
```

Emails will be written to `storage/logs/laravel.log` instead of being sent.

---

## Testing Email Functionality

After setting up credentials, test by:

1. **Create a new task** and assign it to an employee whose `preferred_channel` is set to `email`
2. **Check your email inbox** (or Mailtrap inbox)
3. You should see an email with subject: "New Task Assigned: [Task Title]"

### Set User Email Preference
Update a user to receive email notifications:

```sql
UPDATE users SET preferred_channel = 'email' WHERE id = 1;
```

Or via Tinker:
```bash
php artisan tinker
> $user = User::find(1);
> $user->preferred_channel = 'email';
> $user->save();
```

---

## What Emails Are Sent?

The application sends these email notifications:

1. **TaskAssigned** - When a task is assigned to an employee
2. **TaskRescheduled** - When a task's schedule is changed
3. **TaskStatusUpdated** - When task status changes
4. **TaskOverdue** - When a task becomes overdue

All notifications also create in-app notifications in the database regardless of email preference.

---

## After Configuration

Don't forget to clear config cache:
```bash
php artisan config:clear
```

---

## Recommended Setup

**For Development:** Use Mailtrap
**For Production:** Use SendGrid, Mailgun, or Amazon SES
