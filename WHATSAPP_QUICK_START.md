# WhatsApp Notifications - Quick Start ✅

## What Was Added

✅ **WhatsApp Channel Support** with 5 providers
✅ **Multi-provider configuration** in `.env`
✅ **Test command** to verify setup
✅ **Comprehensive documentation**

---

## Current Status

The WhatsApp channel is **configured but not activated** (no credentials yet).

**To activate:**
1. Choose a WhatsApp provider
2. Get API credentials
3. Update `.env` file
4. Test with `php artisan whatsapp:test`

---

## Supported WhatsApp Providers

| Provider | Best For | Monthly Cost | Setup Time |
|----------|----------|--------------|------------|
| **Meta Cloud API** ⭐ | FREE! Official! | **FREE** (1000 conv) | 1-3 weeks |
| **Twilio** | Quick testing | Pay as you go | 10 min |
| **WasenderAPI** | Budget-friendly | $6 (unlimited) | 5 min |
| **Whapi** | Modern API | $29 (unlimited) | 10 min |
| **UltraMsg** | Quick setup | $39 (unlimited) | 5 min |
| **WATI** | Business features | $49+ | 20 min |
| **Vonage** | Global reach | Pay as you go | 30 min |

**Recommended:** **Meta Cloud API** for production (FREE!), **Twilio** for testing now

---

## Quick Setup (UltraMsg - 5 Minutes)

### Step 1: Create Account
1. Go to https://ultramsg.com
2. Sign up (free trial available)
3. Scan QR code with your WhatsApp

### Step 2: Get Credentials
1. Dashboard → API
2. Copy **Instance ID** (e.g., `instance12345`)
3. Copy **API Token**

### Step 3: Update `.env`
```env
WHATSAPP_PROVIDER=ultramsg
ULTRAMSG_INSTANCE_ID=instance12345
ULTRAMSG_TOKEN=your_api_token_here
```

### Step 4: Test
```bash
php artisan config:clear
php artisan whatsapp:test +263771234567
```

**Done!** Workers can now receive WhatsApp notifications.

---

## How to Enable WhatsApp for Workers

### Option 1: For Specific Worker
```bash
php artisan tinker
```

```php
$worker = User::find(5);  // Kevy
$worker->preferred_channel = 'whatsapp';
$worker->phone = '+263771234567';  // Must include country code!
$worker->save();
```

### Option 2: For All Workers
```php
User::where('role', 'employee')->update(['preferred_channel' => 'whatsapp']);

// Don't forget to update phone numbers!
$worker = User::find(5);
$worker->phone = '+263771234567';
$worker->save();
```

---

## Test WhatsApp Notifications

### Method 1: Test Command
```bash
php artisan whatsapp:test +263771234567
```

This will:
- Check your configuration
- Show provider details
- Send a test message

### Method 2: Assign a Task
1. Set worker's `preferred_channel` to `whatsapp`
2. Assign a task to that worker
3. They receive WhatsApp notification!

---

## Phone Number Format

**IMPORTANT:** Phone numbers MUST include country code!

✅ **Correct:**
- `+263771234567` (Zimbabwe)
- `+27821234567` (South Africa)
- `+254721234567` (Kenya)

❌ **Wrong:**
- `0771234567` (missing country code)
- `263771234567` (missing +)
- `+263 77 123 4567` (has spaces)

---

## Configuration Files

### `.env` - WhatsApp Settings Added
```env
# WhatsApp Configuration
WHATSAPP_PROVIDER=twilio

# UltraMsg (Recommended)
ULTRAMSG_INSTANCE_ID=
ULTRAMSG_TOKEN=

# Twilio
TWILIO_SID=
TWILIO_TOKEN=
TWILIO_WHATSAPP_FROM=whatsapp:+14155238886

# Whapi
WHAPI_API_URL=
WHAPI_TOKEN=

# WATI
WATI_API_URL=
WATI_ACCESS_TOKEN=

# Vonage
VONAGE_API_KEY=
VONAGE_API_SECRET=
VONAGE_WHATSAPP_NUMBER=
```

### `config/services.php` - WhatsApp Providers
All 5 providers configured with fallback values.

### `app/Channels/WhatsappChannel.php` - Updated
Now supports multiple providers with automatic routing.

---

## Available Commands

```bash
# Test WhatsApp configuration and send test message
php artisan whatsapp:test +263771234567

# Interactive mode (asks for phone number)
php artisan whatsapp:test

# Check worker notification settings
php artisan worker:email list

# Clear config cache after .env changes
php artisan config:clear
```

---

## Message Examples

### Task Assigned
```
New Task Assigned: Fix TVs
Scheduled: Dec 07, 2025 10:11
Priority: High
```

### Task Overdue
```
⚠️ Task Overdue: Fix TVs
Was due: Dec 07, 2025 10:11
Priority: High
Please complete as soon as possible!
```

### Task Rescheduled
```
Task Rescheduled: Fix TVs
New Schedule: Dec 08, 2025 14:00
```

---

## Current Worker Settings

Run this to see who can receive WhatsApp:
```bash
php artisan worker:email list
```

Example output:
```
+----+-------+---------------------------+----------+
| ID | Name  | Email                     | Channel  |
+----+-------+---------------------------+----------+
| 5  | Kevy  | kelvintchisanga@gmail.com | email    |
+----+-------+---------------------------+----------+
```

To change to WhatsApp:
```bash
php artisan tinker
$user = User::find(5);
$user->preferred_channel = 'whatsapp';
$user->phone = '+263771234567';
$user->save();
```

---

## Notification Channels Available

Your system now supports **4 notification channels**:

1. **Email** ✅ (Configured with Gmail)
2. **WhatsApp** ✅ (Configured, needs credentials)
3. **SMS** ⚠️ (Available, needs setup)
4. **In-app** ✅ (Always available)

Workers choose their preferred channel, and notifications are sent accordingly!

---

## Cost Comparison (100 workers, 10 msgs/month)

| Provider | Monthly Cost | Notes |
|----------|--------------|-------|
| WasenderAPI | $6 | Cheapest, unlimited messages |
| Twilio | ~$5 | Pay per message (1000 × $0.005) |
| Whapi | $29 | Unlimited messages |
| UltraMsg | $39 | Unlimited messages |
| WATI | $49+ | Business features |

**Recommendation:** WasenderAPI for budget, Twilio for reliability.

---

## Next Steps

1. **Choose provider** (recommend UltraMsg)
2. **Create account** with provider
3. **Get API credentials**
4. **Update `.env` file**
5. **Run** `php artisan config:clear`
6. **Test** with `php artisan whatsapp:test +263771234567`
7. **Update worker phone numbers** with country code
8. **Set workers** to `preferred_channel = 'whatsapp'`
9. **Assign tasks** and watch WhatsApp notifications arrive!

---

## Documentation

- **WHATSAPP_SETUP_GUIDE.md** - Detailed setup for all 5 providers
- **WHATSAPP_QUICK_START.md** - This file (quick overview)
- **WORKER_EMAIL_SETUP.md** - Worker notification settings
- **EMAIL_SYSTEM_COMPLETE.md** - Email notification system

---

## Troubleshooting

**No credentials?**
- System will log warnings but won't crash
- Check `storage/logs/laravel.log`

**Test command fails?**
- Check `.env` has correct provider credentials
- Run `php artisan config:clear`
- Verify phone number format (+263...)

**Worker not receiving?**
- Check `preferred_channel` is `'whatsapp'`
- Check `phone` field has number with country code
- Check queue worker is running

**Need help?**
See `WHATSAPP_SETUP_GUIDE.md` for detailed instructions per provider.

---

## Summary

🎉 **WhatsApp Notifications Added!**

- ✅ 5 providers supported
- ✅ Easy switching between providers
- ✅ Test command included
- ✅ Works with existing notification system
- ⚠️ Needs provider credentials to activate

**To activate:** Choose a provider, get credentials, update `.env`, test!

**Recommended:** UltraMsg ($5/month, easiest setup)
