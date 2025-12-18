# Email & WhatsApp Notifications - Deployment Guide

## ✅ What's Configured

Both **Email** and **WhatsApp** notifications are fully implemented and tested for:
- ✅ User Invitations
- ✅ Task Assignments
- ✅ Task Status Updates
- ✅ Task Rescheduling
- ✅ Overdue Task Alerts

## 📧 Email Setup

### Gmail SMTP (Already Configured)
The app is configured to use Gmail SMTP for sending emails.

**Current Credentials:**
- Host: `smtp.gmail.com`
- Port: `587`
- Encryption: `tls`
- Username: `tadiwaroyalty@gmail.com`
- App Password: Already set in `.env`

### Railway Environment Variables

Add these to your Railway project:

```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=tadiwaroyalty@gmail.com
MAIL_PASSWORD=ucrt bjcx dtpk xfqf
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=tadiwaroyalty@gmail.com
MAIL_FROM_NAME=Task Manager

# Optional: Custom addresses for different notification types
MAIL_INVITE_ADDRESS=invites@taskmanager.com
MAIL_INVITE_NAME=Task Manager Invitations
MAIL_TASK_ADDRESS=tasks@taskmanager.com
MAIL_TASK_NAME=Task Manager Tasks
```

## 📱 WhatsApp Setup (Meta/Facebook)

### Current Configuration
Provider: **Meta WhatsApp Business API**

**Already Configured:**
- Token: Set
- Phone ID: `883935854807748`
- Business ID: `756108340078871`
- API Version: `v21.0`

### Railway Environment Variables

Add these to your Railway project:

```env
WHATSAPP_PROVIDER=meta
WHATSAPP_USE_TEMPLATES=false
META_WHATSAPP_TOKEN=EAAThMZC5ml10BQAQZBgMIErQHG5agMblniQN83wo4zbG8eHHjTED02PFd1ZB3ere3o8mHm4ZBvp3Qegc0pRC5apj9ogDaySk8ZA5U3qKr1G7ZCXDPXmgHF8edHPwXu2xZBRZAhDnYYOMv8m4rrTDcIrEcHhDnqkYXOZAeBauagZBZCuUYRSlbQIIVNMPIxPgQUmeIANHkZBqdZCEkxmhpniU1NGT22JnOVpgEtF2Dda6ZAifhmv4p7nkmw1zJ6ZCZCkF9OwUAbrWaYMSJuY7qlZAo9s6J6wjnokBT
META_WHATSAPP_PHONE_ID=883935854807748
META_WHATSAPP_BUSINESS_ID=756108340078871
META_WHATSAPP_VERSION=v21.0
```

### Optional: WhatsApp Templates

If you want to use WhatsApp message templates (for better delivery rates):

1. Create templates in Meta Business Manager
2. Set `WHATSAPP_USE_TEMPLATES=true`
3. Add template names:

```env
WHATSAPP_USE_TEMPLATES=true
WHATSAPP_TEMPLATE_USER_INVITATION=your_invitation_template
WHATSAPP_TEMPLATE_TASK_ASSIGNED=your_task_template
WHATSAPP_TEMPLATE_TASK_RESCHEDULED=your_reschedule_template
WHATSAPP_TEMPLATE_TASK_STATUS=your_status_template
WHATSAPP_TEMPLATE_TASK_OVERDUE=your_overdue_template
```

## 🧪 Testing

### Local Testing
```bash
php artisan test:notifications --email=your@email.com --phone=+263771234567
```

### Production Testing (on Railway)
```bash
railway run php artisan test:notifications --email=your@email.com --phone=+263771234567
```

## 🚀 Deployment Steps for Railway

### 1. Set Environment Variables

In Railway Dashboard:
1. Go to your project
2. Click on "Variables" tab
3. Add all the environment variables listed above
4. Click "Deploy"

### 2. Verify Queue Worker (Important!)

Since notifications use `ShouldQueue`, ensure your queue worker is running:

**Option A: Add to Procfile**
```
web: php artisan serve --host=0.0.0.0 --port=$PORT
worker: php artisan queue:work --tries=3
```

**Option B: Use Railway's Background Process**
Add a second service with:
- Start Command: `php artisan queue:work --tries=3`

### 3. Test After Deployment

1. Invite a new user from the UI
2. Assign a task to a user
3. Check:
   - Email inbox (spam folder too)
   - WhatsApp messages

## 📊 Monitoring

### Check Logs
```bash
# On Railway
railway logs

# Look for:
# - "Meta WhatsApp message sent"
# - Mail sending confirmations
# - Any errors
```

### Common Issues

**Email not sending:**
- ✅ Check Gmail app password is correct
- ✅ Check "Less secure app access" is enabled (if using regular password)
- ✅ Check spam folder

**WhatsApp not sending:**
- ✅ Verify Meta token hasn't expired
- ✅ Check phone number format (+263...)
- ✅ Verify WhatsApp Business Account is active
- ✅ Check Meta API logs in Business Manager

## 🔐 Security Notes

1. **Never commit credentials** - They're in `.env` which is gitignored
2. **Use Railway's encrypted variables** for production
3. **Rotate tokens periodically** - Update both Gmail and Meta tokens
4. **Monitor usage** - Check Meta Business Manager for API limits

## 📝 How Notifications Work

### User Invitation Flow
1. Admin invites user via `/admin/users/invite`
2. System creates `InvitedUser` record
3. `UserInvited` notification sent via:
   - ✅ Email (always)
   - ✅ WhatsApp (if phone number provided)

### Task Assignment Flow
1. Task created/assigned via `/tasks/create` or `/tasks/{id}/edit`
2. `TaskAssigned` notification sent based on user's `preferred_channel`:
   - `email` → Email only
   - `whatsapp` → WhatsApp only
   - `sms` → SMS only
3. Also saved to database for in-app notifications

## 🎯 Production Ready

✅ Email configured with Gmail SMTP
✅ WhatsApp configured with Meta API
✅ Notifications queued for async processing
✅ Error logging enabled
✅ Test command available
✅ Works on both local and Railway

**Status: Ready for Production Deployment** 🚀
