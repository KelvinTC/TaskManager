# Twilio WhatsApp Setup Guide

## Why Twilio?
✅ **$15 free credit** for testing
✅ Enterprise-grade reliability
✅ Pay-as-you-go pricing (~$0.005 per message)
✅ Excellent documentation
✅ Global reach

---

## Step-by-Step Setup

### Step 1: Create Twilio Account (5 minutes)

1. **Go to Twilio**
   - Visit https://www.twilio.com/try-twilio
   - Click "Sign up and start building"

2. **Fill in Details**
   - First name, Last name
   - Email address
   - Password
   - Click "Start your free trial"

3. **Verify Email**
   - Check your email
   - Click verification link

4. **Verify Phone Number**
   - Enter your phone number
   - Enter verification code sent via SMS

5. **Complete Setup**
   - Answer a few questions about your use case
   - Select "Products: Messaging"
   - Select "Use WhatsApp"

✅ **You now have $15 free credit!**

---

### Step 2: Get Your Twilio Credentials (2 minutes)

1. **Go to Dashboard**
   - https://console.twilio.com/

2. **Find Account Info**
   - On the dashboard, you'll see:
     - **Account SID** (looks like: `ACxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx`)
     - **Auth Token** (click "Show" to reveal)

3. **Copy These Values**
   - Copy Account SID
   - Copy Auth Token
   - Keep them safe!

---

### Step 3: Enable WhatsApp (10 minutes)

#### Option A: WhatsApp Sandbox (For Testing - Instant Setup)

1. **Go to Messaging**
   - In Twilio Console
   - Navigate to: Messaging → Try it out → Send a WhatsApp message

2. **Join Sandbox**
   - You'll see a sandbox number (e.g., `+1 415 523 8886`)
   - You'll see a join code (e.g., `join <code>`)

3. **Activate Your WhatsApp**
   - Open WhatsApp on your phone
   - Send a message to the sandbox number: `join <your-code>`
   - Example: `join happy-tiger-123`
   - You'll receive a confirmation message

4. **Get Sandbox Number**
   - Your sandbox WhatsApp number is: `whatsapp:+14155238886`

✅ **Sandbox is active! Good for testing.**

⚠️ **Note:** Sandbox has limitations:
- Only people who "join" can receive messages
- For production, you need WhatsApp Business API (see Option B)

#### Option B: WhatsApp Business API (For Production)

1. **Request Access**
   - Go to: Messaging → WhatsApp → Get started
   - Click "Request Access"

2. **Business Verification**
   - Submit business details
   - Facebook Business Manager verification required
   - Can take 1-3 weeks for approval

3. **Get WhatsApp Business Number**
   - Once approved, you'll get a WhatsApp Business number
   - Format: `whatsapp:+1234567890`

For now, **use Sandbox for testing**. You can upgrade to Business API later.

---

### Step 4: Install Twilio SDK

```bash
cd /home/kc/PhpstormProjects/smartwork/task-scheduling-system
composer require twilio/sdk
```

---

### Step 5: Update .env File

```bash
nano .env
```

Add these lines (or update existing ones):

```env
# WhatsApp Configuration
WHATSAPP_PROVIDER=twilio

# Twilio WhatsApp Credentials
TWILIO_SID=ACxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
TWILIO_TOKEN=your_auth_token_here
TWILIO_WHATSAPP_FROM=whatsapp:+14155238886
```

**Replace:**
- `ACxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx` with your Account SID
- `your_auth_token_here` with your Auth Token
- Keep `whatsapp:+14155238886` (Twilio sandbox number)

---

### Step 6: Clear Config Cache

```bash
php artisan config:clear
```

---

### Step 7: Test Your Setup

```bash
php artisan whatsapp:test +263771234567
```

**What this does:**
- Checks Twilio configuration
- Verifies credentials
- Sends a test WhatsApp message

**Expected output:**
```
Testing WhatsApp configuration...

Provider: twilio
Configuration found ✓

SID: ✓ Set
Token: ✓ Set
From: whatsapp:+14155238886

Send test WhatsApp message to +263771234567? (yes/no) [yes]:
> yes

Sending test message...

✓ Message sent successfully!
Check WhatsApp on: +263771234567

Note: Message may take a few seconds to arrive.
```

---

### Step 8: Join Sandbox with Test Number

**IMPORTANT:** Before the test number can receive messages:

1. **From your test phone** (+263771234567)
2. **Open WhatsApp**
3. **Send to:** `+1 415 523 8886`
4. **Message:** `join <your-code>` (find code in Twilio Console)
5. **Wait for confirmation**

Then run the test again:
```bash
php artisan whatsapp:test +263771234567
```

---

### Step 9: Enable WhatsApp for Workers

#### For Kevy:

```bash
php artisan tinker
```

```php
$kevy = User::find(5);
$kevy->preferred_channel = 'whatsapp';
$kevy->phone = '+263771234567';
$kevy->save();

// Kevy needs to join the Twilio sandbox first!
// From Kevy's phone, send to +1 415 523 8886: join <code>
```

#### Check Worker Settings:

```bash
php artisan worker:email list
```

---

### Step 10: Test with Real Task Assignment

1. **Make sure queue worker is running:**
```bash
# Check if running
ps aux | grep queue:work

# If not running, start it
php artisan queue:work --tries=3 --timeout=90 &
```

2. **Assign a task to Kevy via the web UI**

3. **Check Kevy's WhatsApp!**

Message will look like:
```
New Task Assigned: Fix TVs
Scheduled: Dec 07, 2025 10:11
Priority: High
```

---

## Twilio Sandbox Limitations

⚠️ **Sandbox is for TESTING only:**
- Workers must "join" the sandbox before receiving messages
- 24-hour session (workers rejoin after inactivity)
- Template messages have restrictions

✅ **For Production:**
- Apply for WhatsApp Business API
- No join required
- No session limits
- Full template support

---

## Pricing

### Free Trial:
- **$15 free credit**
- No credit card required initially
- Expires after trial period or when credit runs out

### After Trial:
- **$0.005 per message** (half a cent)
- **WhatsApp Business API:** $0.005 - $0.01 per message
- **100 workers, 10 msgs/month = 1000 messages:**
  - Cost: 1000 × $0.005 = **$5/month**

### No Monthly Fee:
- Only pay for what you use
- No subscription required

---

## Multiple Workers Setup

To enable all workers to receive WhatsApp:

```bash
php artisan tinker
```

```php
// Update all workers
$workers = User::where('role', 'employee')->get();

foreach ($workers as $worker) {
    // Update their channel
    $worker->preferred_channel = 'whatsapp';

    // IMPORTANT: Set their phone number with country code
    // Example for Zimbabwe numbers:
    if ($worker->id == 3) {
        $worker->phone = '+263771234567';
    } elseif ($worker->id == 5) {
        $worker->phone = '+263771234568';
    }
    // ... etc for each worker

    $worker->save();

    echo "✓ Updated: {$worker->name}\n";
}

echo "\nDon't forget: Each worker must join the Twilio sandbox!\n";
echo "Send to +1 415 523 8886: join <code>\n";
```

---

## Troubleshooting

### Error: "21606: The From phone number is not a valid"

**Solution:** Make sure `TWILIO_WHATSAPP_FROM` has `whatsapp:` prefix
```env
TWILIO_WHATSAPP_FROM=whatsapp:+14155238886
```

### Error: "21608: User is not currently opted in"

**Solution:** Worker hasn't joined the sandbox
- From worker's WhatsApp
- Send to: `+1 415 523 8886`
- Message: `join <code>`

### No Messages Received?

**Check:**
1. ✅ Worker joined sandbox?
2. ✅ Phone number has country code? (+263...)
3. ✅ Queue worker running?
4. ✅ Check logs: `tail -f storage/logs/laravel.log`

### Twilio SDK Not Found?

**Solution:**
```bash
composer require twilio/sdk
```

---

## Moving to Production

When ready for production (no sandbox limitations):

1. **Apply for WhatsApp Business API**
   - Twilio Console → Messaging → WhatsApp
   - Submit business verification

2. **Get WhatsApp Business Number**
   - Once approved, you'll receive a dedicated number
   - Example: `whatsapp:+14155551234`

3. **Update .env**
```env
TWILIO_WHATSAPP_FROM=whatsapp:+14155551234
```

4. **Workers no longer need to "join"**
   - Messages sent directly
   - No 24-hour session limits

---

## Commands Quick Reference

```bash
# Install Twilio SDK
composer require twilio/sdk

# Clear config
php artisan config:clear

# Test WhatsApp
php artisan whatsapp:test +263771234567

# Check worker settings
php artisan worker:email list

# Start queue worker
php artisan queue:work --tries=3 --timeout=90 &

# Check logs
tail -f storage/logs/laravel.log
```

---

## Summary

✅ **Setup Complete Checklist:**
- [ ] Twilio account created ($15 free credit)
- [ ] Account SID and Auth Token copied
- [ ] WhatsApp Sandbox activated
- [ ] Twilio SDK installed (`composer require twilio/sdk`)
- [ ] `.env` file updated with credentials
- [ ] Config cache cleared
- [ ] Test WhatsApp sent successfully
- [ ] Workers' phone numbers updated
- [ ] Workers joined Twilio sandbox
- [ ] Queue worker running
- [ ] Test task assigned and WhatsApp received!

---

**Next Step:** Follow the steps above and let me know when you have your Twilio Account SID and Auth Token ready!
