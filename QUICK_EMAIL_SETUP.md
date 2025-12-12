# Quick Email Setup - Task Manager

## ✅ Email System is Configured and Ready!

The application is set up to send emails. You just need to add SMTP credentials.

---

## 🚀 Quick Start (3 Minutes)

### Step 1: Get Free Mailtrap Account
1. Go to **https://mailtrap.io/**
2. Click "Sign Up" (it's FREE)
3. Verify your email

### Step 2: Get Your Credentials
1. Go to **Inboxes** tab
2. Click on "My Inbox"
3. You'll see SMTP credentials like:
   ```
   Host: sandbox.smtp.mailtrap.io
   Port: 2525
   Username: abc123def456
   Password: xyz789ghi012
   ```

### Step 3: Update .env File
Open `.env` and update these lines:
```env
MAIL_USERNAME=abc123def456
MAIL_PASSWORD=xyz789ghi012
```
(Replace with your actual Mailtrap credentials)

### Step 4: Clear Cache & Test
```bash
php artisan config:clear
php artisan email:test your-email@example.com
```

You should see: `✓ Email sent successfully!`

Then check your Mailtrap inbox to see the email!

---

## 📧 How to Test Task Notifications

### Enable Email for Workers

**Quick way:**
```bash
# Check which workers have email enabled
php artisan worker:email list

# Enable email for a specific worker
php artisan worker:email enable 3

# Enable email for ALL workers
php artisan worker:email enable-all
```

**Alternative via tinker:**
```bash
php artisan tinker
```
Then run:
```php
$user = User::find(1);
$user->preferred_channel = 'email';
$user->save();
```

Now when you assign a task to that user, they'll receive an email!

---

## 🎯 Current Status

✅ Email system configured
✅ Notifications implemented
✅ Multi-channel support (Email, SMS, WhatsApp, In-app)
⚠️ **Needs SMTP credentials to actually send**

---

## 📝 Test Email Command

We've created a handy test command:

```bash
# Test with any email
php artisan email:test test@example.com

# Interactive mode
php artisan email:test
```

This will show you:
- ✓ If email sent successfully
- Current mail driver
- SMTP configuration status
- Helpful error messages if something's wrong

---

## 🔑 Why Mailtrap?

- ✅ **FREE** forever (100 emails/month free tier)
- ✅ **Safe** - catches all emails, doesn't send to real addresses
- ✅ **See exactly** what emails look like
- ✅ **Perfect for development**

All test emails appear in Mailtrap inbox, not real email accounts!

---

## 🌐 For Production

When ready for production, use:
- **Gmail** (500 emails/day limit)
- **SendGrid** (100 emails/day free)
- **Amazon SES** (very cheap)
- **Mailgun** (5000 emails/month free)

See `EMAIL_SETUP_GUIDE.md` for detailed instructions.

---

## ❓ Troubleshooting

**Error: "Authentication required"**
→ Add MAIL_USERNAME and MAIL_PASSWORD to .env

**Error: "Connection refused"**
→ Check firewall or use port 587 instead of 2525

**No error but no email?**
→ Check `storage/logs/laravel.log` for details

**Still stuck?**
→ Read `EMAIL_SETUP_GUIDE.md` or run `php artisan email:test`
