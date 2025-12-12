# Twilio WhatsApp Setup - Quick Checklist ✅

## What You Need To Do

### 1. Create Twilio Account (5 min)
- [ ] Go to https://www.twilio.com/try-twilio
- [ ] Sign up (get $15 free credit!)
- [ ] Verify your email
- [ ] Verify your phone number

### 2. Get Your Credentials (2 min)
- [ ] Login to https://console.twilio.com/
- [ ] Copy **Account SID** (starts with `AC...`)
- [ ] Copy **Auth Token** (click "Show" to reveal)

### 3. Activate WhatsApp Sandbox (3 min)
- [ ] Go to: Messaging → Try it out → Send a WhatsApp message
- [ ] Note the sandbox number: `+1 415 523 8886`
- [ ] Note your join code (e.g., `join happy-tiger-123`)
- [ ] From YOUR WhatsApp, send to `+1 415 523 8886`: `join <code>`
- [ ] Wait for confirmation message

### 4. Update .env File
- [ ] Open `.env` file
- [ ] Find the WhatsApp section
- [ ] Update these values:

```env
WHATSAPP_PROVIDER=twilio
TWILIO_SID=ACxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
TWILIO_TOKEN=your_auth_token_here
TWILIO_WHATSAPP_FROM=whatsapp:+14155238886
```

### 5. Clear Config & Test
```bash
# Clear config
php artisan config:clear

# Test (make sure you joined the sandbox first!)
php artisan whatsapp:test +263771234567
```

### 6. Enable WhatsApp for Workers
```bash
php artisan tinker
```

```php
// Update Kevy
$kevy = User::find(5);
$kevy->preferred_channel = 'whatsapp';
$kevy->phone = '+263783017279';
$kevy->save();
```

**IMPORTANT:** Kevy must join the Twilio sandbox:
- From Kevy's WhatsApp
- Send to: `+1 415 523 8886`
- Message: `join <your-code>`

### 7. Test with Real Task
- [ ] Queue worker is running: `php artisan queue:work`
- [ ] Assign a task to Kevy
- [ ] Check Kevy's WhatsApp!

---

## Quick Commands

```bash
# Clear config
php artisan config:clear

# Test WhatsApp
php artisan whatsapp:test +263771234567

# Check workers
php artisan worker:email list

# Start queue worker
php artisan queue:work --tries=3 --timeout=90 &

# Check if queue worker is running
ps aux | grep queue:work
```

---

## What To Send Me

Once you've created your Twilio account, send me:

1. **Account SID** (starts with `AC...`)
2. **Auth Token**
3. **Join code** (from sandbox)

I'll help you configure everything!

---

## Troubleshooting

### Can't find Account SID/Token?
- Go to https://console.twilio.com/
- They're on the main dashboard under "Account Info"

### WhatsApp sandbox not working?
- Make sure you sent: `join <code>` (not just the code)
- Send it to: `+1 415 523 8886`
- Wait for "You are all set!" confirmation

### Test command fails?
- Did you join the sandbox first?
- Is your phone number correct? (+263...)
- Run: `php artisan config:clear`

---

## Cost

- **Free trial:** $15 credit (plenty for testing!)
- **After trial:** $0.005 per message (~half a cent)
- **For 100 workers × 10 msgs/month:** ~$5/month
- **No monthly subscription!**

---

## Next Step

👉 **Go to https://www.twilio.com/try-twilio and create your account!**

Then come back with your Account SID and Auth Token, and I'll help you set it up! 🚀
