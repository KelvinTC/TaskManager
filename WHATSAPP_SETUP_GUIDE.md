# WhatsApp Notifications Setup Guide

## Overview

The Task Manager system now supports WhatsApp notifications through **5 different providers**. Choose the one that works best for you based on budget, location, and requirements.

---

## Supported Providers

| Provider | Best For | Pricing | Setup Difficulty |
|----------|----------|---------|------------------|
| **UltraMsg** | Quick setup, affordable | $5/month (1000 msgs) | ⭐ Easy |
| **Whapi** | Modern API, good docs | $29/month | ⭐⭐ Easy |
| **WATI** | Business features | Starting at $49/month | ⭐⭐ Medium |
| **Twilio** | Enterprise, reliable | Pay as you go | ⭐⭐⭐ Medium |
| **Vonage** | Global reach | Pay as you go | ⭐⭐⭐ Medium |

**Recommended:** Start with **UltraMsg** (easiest and cheapest)

---

## Option 1: UltraMsg (Recommended for Beginners)

### Why UltraMsg?
- ✅ **Easiest setup** (5 minutes)
- ✅ **Affordable** ($5/month for 1000 messages)
- ✅ **No business verification** required
- ✅ **Works with personal WhatsApp**

### Setup Steps

1. **Create Account**
   - Go to https://ultramsg.com
   - Sign up (free trial available)
   - Verify your email

2. **Connect WhatsApp**
   - Scan QR code with your WhatsApp
   - Your WhatsApp is now connected!

3. **Get Credentials**
   - Go to Dashboard → API
   - Copy your **Instance ID** (looks like: `instance12345`)
   - Copy your **API Token**

4. **Update `.env`**
```env
WHATSAPP_PROVIDER=ultramsg
ULTRAMSG_INSTANCE_ID=your_instance_id
ULTRAMSG_TOKEN=your_api_token
```

5. **Test**
```bash
php artisan config:clear
php artisan whatsapp:test +263771234567
```

**Done!** Workers with `preferred_channel=whatsapp` will now receive WhatsApp messages.

---

## Option 2: Whapi.cloud

### Why Whapi?
- ✅ Modern, developer-friendly API
- ✅ Good documentation
- ✅ Reliable service
- ⚠️ More expensive than UltraMsg

### Setup Steps

1. **Create Account**
   - Go to https://whapi.cloud
   - Sign up and verify email

2. **Create Channel**
   - Create a new WhatsApp channel
   - Connect your WhatsApp Business number

3. **Get API Credentials**
   - Go to API Settings
   - Copy **API URL** (e.g., `https://gate.whapi.cloud/YOUR_INSTANCE`)
   - Copy **API Token**

4. **Update `.env`**
```env
WHATSAPP_PROVIDER=whapi
WHAPI_API_URL=https://gate.whapi.cloud/your_instance
WHAPI_TOKEN=your_token_here
```

5. **Test**
```bash
php artisan config:clear
php artisan whatsapp:test +263771234567
```

---

## Option 3: WATI

### Why WATI?
- ✅ Business-focused features
- ✅ Team collaboration tools
- ✅ Template management
- ⚠️ Higher cost
- ⚠️ Requires business verification

### Setup Steps

1. **Create Account**
   - Go to https://www.wati.io
   - Sign up for business account

2. **Connect WhatsApp Business**
   - Verify your business
   - Connect WhatsApp Business number

3. **Get API Credentials**
   - Go to Settings → API
   - Copy **API URL**
   - Generate and copy **Access Token**

4. **Update `.env`**
```env
WHATSAPP_PROVIDER=wati
WATI_API_URL=https://live-server-xxxxx.wati.io
WATI_ACCESS_TOKEN=your_access_token
```

5. **Test**
```bash
php artisan config:clear
php artisan whatsapp:test +263771234567
```

---

## Option 4: Twilio

### Why Twilio?
- ✅ Enterprise-grade reliability
- ✅ Global reach
- ✅ Excellent documentation
- ⚠️ Complex setup
- ⚠️ Requires business verification

### Setup Steps

1. **Create Twilio Account**
   - Go to https://www.twilio.com/try-twilio
   - Sign up and verify your account

2. **Enable WhatsApp**
   - Go to Messaging → Try WhatsApp
   - Follow sandbox setup (for testing)
   - For production: Submit business verification

3. **Get Credentials**
   - Dashboard → Account Info
   - Copy **Account SID**
   - Copy **Auth Token**
   - Copy **WhatsApp-enabled phone number**

4. **Install Twilio SDK**
```bash
composer require twilio/sdk
```

5. **Update `.env`**
```env
WHATSAPP_PROVIDER=twilio
TWILIO_SID=ACxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
TWILIO_TOKEN=your_auth_token
TWILIO_WHATSAPP_FROM=whatsapp:+14155238886
```

6. **Test**
```bash
php artisan config:clear
php artisan whatsapp:test +263771234567
```

---

## Option 5: Vonage (Nexmo)

### Why Vonage?
- ✅ Good global coverage
- ✅ Multiple channels (SMS, WhatsApp, etc.)
- ⚠️ Complex pricing
- ⚠️ Requires business verification

### Setup Steps

1. **Create Vonage Account**
   - Go to https://dashboard.nexmo.com/sign-up
   - Create account

2. **Enable WhatsApp**
   - Go to Messages API → WhatsApp
   - Set up WhatsApp sandbox (for testing)
   - For production: Apply for business account

3. **Get API Credentials**
   - Dashboard → API Settings
   - Copy **API Key**
   - Copy **API Secret**
   - Copy **WhatsApp number**

4. **Update `.env`**
```env
WHATSAPP_PROVIDER=vonage
VONAGE_API_KEY=your_api_key
VONAGE_API_SECRET=your_api_secret
VONAGE_WHATSAPP_NUMBER=447700900000
```

5. **Test**
```bash
php artisan config:clear
php artisan whatsapp:test +263771234567
```

---

## How to Enable WhatsApp for Workers

### Method 1: Use Worker Management Command
```bash
# Check current settings
php artisan worker:email list

# Change a worker to WhatsApp
php artisan tinker
```

Then:
```php
$worker = User::find(5);  // Kevy
$worker->preferred_channel = 'whatsapp';
$worker->save();
```

### Method 2: Update All Workers
```php
User::where('role', 'employee')->update(['preferred_channel' => 'whatsapp']);
```

---

## Testing WhatsApp Notifications

### Create a Test Command

I'll create a test command for you:

```bash
php artisan make:command TestWhatsapp
```

### Test Manually

```bash
php artisan tinker
```

```php
$user = User::find(5);  // Replace with worker ID
$user->preferred_channel = 'whatsapp';
$user->phone = '+263771234567';  // Worker's WhatsApp number
$user->save();

$task = Task::find(1);  // Replace with task ID
$user->notify(new \App\Notifications\TaskAssigned($task));
```

Check the worker's WhatsApp!

---

## Phone Number Format

**Important:** Phone numbers must be in international format:

✅ **Correct:**
- `+263771234567` (Zimbabwe)
- `+27821234567` (South Africa)
- `+254721234567` (Kenya)

❌ **Wrong:**
- `0771234567` (missing country code)
- `263771234567` (missing +)
- `+263 77 123 4567` (has spaces)

---

## Message Examples

When a task is assigned, workers receive:

```
New Task Assigned: Fix TVs
Scheduled: Dec 07, 2025 10:11
Priority: High
```

When a task is overdue:

```
⚠️ Task Overdue: Fix TVs
Was due: Dec 07, 2025 10:11
Priority: High
Please complete as soon as possible!
```

---

## Pricing Comparison

| Provider | Monthly Cost | Per Message | Free Trial |
|----------|--------------|-------------|------------|
| UltraMsg | $5 (1000 msgs) | $0.005 | 3 days |
| Whapi | $29 | Unlimited | 7 days |
| WATI | $49+ | Included | Demo |
| Twilio | Pay as you go | $0.005 | $15 credit |
| Vonage | Pay as you go | $0.0057 | €2 credit |

---

## Troubleshooting

### WhatsApp Messages Not Sending?

**Check 1: Is provider configured?**
```bash
php artisan tinker
config('services.whatsapp.provider')
```

**Check 2: Are credentials set?**
```bash
php artisan config:clear
# Check .env file has correct credentials
```

**Check 3: Is phone number valid?**
- Must include country code (+263)
- No spaces or special characters
- Format: `+263771234567`

**Check 4: Check logs**
```bash
tail -f storage/logs/laravel.log
```

### Worker Not Receiving Messages?

**Check preferred_channel:**
```php
User::find(5)->preferred_channel  // Should be 'whatsapp'
```

**Check phone number:**
```php
User::find(5)->phone  // Should be +263771234567
```

**Update if needed:**
```php
$user = User::find(5);
$user->preferred_channel = 'whatsapp';
$user->phone = '+263771234567';
$user->save();
```

---

## Quick Setup Checklist

- [ ] Choose WhatsApp provider
- [ ] Create account with provider
- [ ] Get API credentials
- [ ] Update `.env` file
- [ ] Run `php artisan config:clear`
- [ ] Update worker phone numbers
- [ ] Set worker `preferred_channel` to `whatsapp`
- [ ] Test with a task assignment

---

## Recommendation

For Zimbabwe-based workers:

1. **Start with UltraMsg** (cheapest, easiest)
2. If you need business features, use **WATI**
3. For enterprise reliability, use **Twilio**

**Cost estimate for 100 workers receiving 10 messages/month:**
- UltraMsg: $5/month (covers 1000 messages)
- Whapi: $29/month (unlimited)
- Twilio: ~$5/month (1000 messages)

---

## Next Steps

1. Choose your provider
2. Follow the setup steps above
3. Update worker phone numbers with country code
4. Set workers to receive WhatsApp notifications
5. Test by assigning a task!

Need help? Check the logs: `tail -f storage/logs/laravel.log`
