# WhatsApp Enterprise Setup Guide

## Table of Contents
1. [Meta Business Verification](#meta-business-verification)
2. [Phone Number Setup](#phone-number-setup)
3. [Template Messages](#template-messages)
4. [Production Configuration](#production-configuration)
5. [Queue & Performance](#queue--performance)
6. [Security & Best Practices](#security--best-practices)
7. [Monitoring & Logging](#monitoring--logging)
8. [Scaling Considerations](#scaling-considerations)

---

## 1. Meta Business Verification

### Step 1: Complete Business Verification
1. Go to [Meta Business Manager](https://business.facebook.com)
2. Navigate to **Business Settings** → **Security Center**
3. Click **Start Verification**
4. Provide:
   - Business legal name
   - Business address
   - Business documents (registration certificate, tax ID, etc.)
   - Phone number
   - Website

### Step 2: WhatsApp Business Platform Access
1. In Business Manager → **WhatsApp Accounts**
2. Request **Official Business Account** (OBA)
3. Display name review (up to 24 hours)
4. Once approved, you can message any number globally

### Step 3: Phone Number Verification
- Use a dedicated business phone number
- Verify via SMS or voice call
- This number becomes your WhatsApp Business number
- **Recommended:** Use a separate number from personal WhatsApp

---

## 2. Phone Number Setup

### Production Phone Number Options

#### Option A: Meta Hosted Number
- Meta provides a phone number
- No SIM card needed
- Limited to certain countries

#### Option B: Own Phone Number (Recommended)
- Use your company's phone number
- More professional (customers recognize it)
- Requires verification

### Configuration
```env
META_WHATSAPP_PHONE_ID=your_phone_number_id
META_WHATSAPP_BUSINESS_ID=your_business_account_id
```

---

## 3. Template Messages

### Why Templates for Enterprise?

**Current Setup:** Plain text messages
- ✅ Simple to implement
- ✅ No approval needed
- ⚠️ Limited to 24-hour conversation windows
- ❌ Can't initiate conversations after 24 hours

**Template Messages:**
- ✅ Can initiate conversations anytime
- ✅ Professional branded messages
- ✅ Marketing campaigns allowed
- ✅ Higher trust with customers
- ⚠️ Requires Meta approval (1-2 days)

### Creating Templates

1. Go to Meta Business Manager → **WhatsApp Manager** → **Message Templates**
2. Click **Create Template**
3. Choose category:
   - **UTILITY** (recommended for your use case): Order updates, task notifications, reminders
   - **AUTHENTICATION**: OTP, verification codes
   - **MARKETING**: Promotional messages (requires opt-in)

### Example Templates for Your App

#### Template 1: Task Assignment
```
Name: task_assigned
Category: UTILITY
Language: English

Header: None

Body:
📋 *New Task Assigned*

*{{1}}*

📅 Scheduled: {{2}}
{{3}} Priority: {{4}}

View full details: {{5}}

Footer: Task Manager - Stay Organized
```

#### Template 2: User Invitation
```
Name: user_invitation
Category: UTILITY
Language: English

Body:
🎉 Welcome to {{1}}!

You have been invited by *{{2}}*

📋 Role: {{3}}
📧 Email: {{4}}

Please complete your registration:
{{5}}

⚠️ Use email {{4}} when registering.

Footer: Welcome to the team! 🚀
```

#### Template 3: Task Overdue
```
Name: task_overdue
Category: UTILITY
Language: English

Body:
🚨 *TASK OVERDUE*

*{{1}}*

⏰ Was due: {{2}}
{{3}} Priority: {{4}}

⚠️ Please complete ASAP!

View task: {{5}}

Footer: Task Manager
```

### Submit Templates for Approval
- Fill in sample data
- Submit for review
- Approval typically takes 12-24 hours
- Once approved, update your config

```env
WHATSAPP_USE_TEMPLATES=true
WHATSAPP_TEMPLATE_USER_INVITATION=user_invitation
WHATSAPP_TEMPLATE_TASK_ASSIGNED=task_assigned
WHATSAPP_TEMPLATE_TASK_RESCHEDULED=task_rescheduled
WHATSAPP_TEMPLATE_TASK_STATUS=task_status_updated
WHATSAPP_TEMPLATE_TASK_OVERDUE=task_overdue
```

---

## 4. Production Configuration

### Environment Variables (.env)

```env
# Meta Cloud API - Production
WHATSAPP_PROVIDER=meta
WHATSAPP_USE_TEMPLATES=true

# Get these from Meta Business Manager
META_WHATSAPP_TOKEN=EAAxxxxxxxxxxxxx  # Long-lived access token
META_WHATSAPP_PHONE_ID=your_phone_id
META_WHATSAPP_BUSINESS_ID=your_business_id
META_WHATSAPP_VERSION=v21.0

# Queue Configuration (Important!)
QUEUE_CONNECTION=redis  # Use Redis for production (not database)

# Logging
LOG_CHANNEL=stack
LOG_LEVEL=info
```

### Long-lived Access Token

**Current:** Short-lived token (expires in 1-2 hours)
**Production:** Long-lived token (60 days, auto-renewable)

To get long-lived token:
1. Go to Meta Business Manager
2. **WhatsApp** → **API Setup**
3. **Generate Access Token**
4. Select permissions: `whatsapp_business_messaging`
5. Copy the token
6. Set token to never expire OR implement auto-refresh

---

## 5. Queue & Performance

### Why Redis for Enterprise?

**Current:** Database queue
- ⚠️ Slower for high volume
- ⚠️ Can cause database bottlenecks

**Production:** Redis queue
- ✅ Much faster
- ✅ Handles thousands of jobs/minute
- ✅ Better for concurrent workers

### Install Redis

```bash
# Ubuntu/Debian
sudo apt update
sudo apt install redis-server
sudo systemctl enable redis-server
sudo systemctl start redis-server

# Install PHP Redis extension
sudo apt install php-redis

# Verify
redis-cli ping  # Should return PONG
```

### Update Laravel Config

```env
QUEUE_CONNECTION=redis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379
```

### Queue Workers Setup

Create systemd service for queue workers:

```bash
sudo nano /etc/systemd/system/task-manager-queue.service
```

```ini
[Unit]
Description=Task Manager Queue Worker
After=network.target

[Service]
Type=simple
User=www-data
WorkingDirectory=/path/to/task-scheduling-system
ExecStart=/usr/bin/php artisan queue:work redis --sleep=3 --tries=3 --max-time=3600 --timeout=60
Restart=always
RestartSec=10

[Install]
WantedBy=multi-user.target
```

Enable and start:
```bash
sudo systemctl enable task-manager-queue
sudo systemctl start task-manager-queue
sudo systemctl status task-manager-queue
```

### Multiple Workers (High Volume)

```bash
# Run 3 workers for better performance
php artisan queue:work redis --queue=default --sleep=3 --tries=3 &
php artisan queue:work redis --queue=default --sleep=3 --tries=3 &
php artisan queue:work redis --queue=default --sleep=3 --tries=3 &
```

Or use Laravel Horizon (recommended):
```bash
composer require laravel/horizon
php artisan horizon:install
php artisan horizon
```

---

## 6. Security & Best Practices

### Access Token Security

**Never commit tokens to Git!**

```bash
# Add to .gitignore
.env
.env.production
.env.backup
```

**Use environment-specific configs:**
```
.env.production  # Production server
.env.staging     # Staging server
.env.local       # Local development
```

### Rate Limiting

WhatsApp has rate limits:
- **1,000 messages/day** (new business accounts)
- **10,000 messages/day** (verified businesses)
- **100,000+ messages/day** (after messaging tier increases)

Implement rate limiting in your app:

```php
// config/services.php
'whatsapp' => [
    'rate_limit' => [
        'enabled' => true,
        'max_per_minute' => 60,
        'max_per_day' => 1000,
    ],
],
```

### Fallback Channels

Always have backup notification channels:

```php
// In your notifications
public function via($notifiable)
{
    $channels = ['database'];

    switch ($notifiable->preferred_channel) {
        case 'whatsapp':
            $channels[] = WhatsappChannel::class;
            $channels[] = 'mail';  // Fallback to email
            break;
        case 'email':
            $channels[] = 'mail';
            break;
    }

    return $channels;
}
```

---

## 7. Monitoring & Logging

### Enhanced Logging

Create dedicated WhatsApp log channel:

```php
// config/logging.php
'channels' => [
    'whatsapp' => [
        'driver' => 'daily',
        'path' => storage_path('logs/whatsapp.log'),
        'level' => 'debug',
        'days' => 14,
    ],
],
```

Update WhatsappChannel to use dedicated log:

```php
use Illuminate\Support\Facades\Log;

Log::channel('whatsapp')->info('Message sent', [
    'to' => $to,
    'message_id' => $response->json('messages.0.id'),
    'timestamp' => now(),
]);
```

### Monitoring Dashboard

Track key metrics:
- Messages sent/failed
- Delivery rates
- Queue size
- Average processing time
- Conversation costs

Consider using:
- Laravel Pulse (built-in monitoring)
- Grafana + Prometheus
- Meta Business Manager Analytics

### Failed Job Handling

```php
// config/queue.php
'failed' => [
    'driver' => 'database',
    'database' => 'mysql',
    'table' => 'failed_jobs',
],
```

Monitor failed jobs:
```bash
php artisan queue:failed
php artisan queue:retry all
```

---

## 8. Scaling Considerations

### For Small Teams (< 100 users)
- ✅ Database queue OK
- ✅ Single queue worker
- ✅ Shared hosting possible
- ✅ Free tier (1,000 conversations/month)

### For Medium Teams (100-1,000 users)
- ✅ Redis queue required
- ✅ 2-3 queue workers
- ✅ VPS/dedicated server
- ✅ Paid tier (~$0.005-0.009 per conversation)
- ✅ Multiple notification channels

### For Large Organizations (1,000+ users)
- ✅ Redis cluster
- ✅ 5-10 queue workers (or Horizon)
- ✅ Load balancer
- ✅ Dedicated Redis server
- ✅ CDN for static assets
- ✅ Database replication
- ✅ Template messages mandatory
- ✅ Analytics & monitoring essential

### Cost Estimation

**Meta WhatsApp Pricing (approximate):**
- First 1,000 conversations/month: FREE
- User-initiated conversations: FREE
- Business-initiated conversations: $0.005 - $0.05 per conversation (varies by country)

**Example for 500 employees:**
- Average 20 task notifications/user/month
- 500 × 20 = 10,000 messages/month
- ~3,000 conversations (24-hour windows)
- Cost: ~$15-150/month (depending on country)

---

## 9. Implementation Checklist

### Phase 1: Preparation (Week 1)
- [ ] Complete Meta Business Verification
- [ ] Verify business phone number
- [ ] Create message templates
- [ ] Submit templates for approval
- [ ] Request production access

### Phase 2: Infrastructure (Week 2)
- [ ] Install and configure Redis
- [ ] Set up queue workers as systemd service
- [ ] Configure long-lived access token
- [ ] Set up monitoring/logging
- [ ] Configure backup notification channels

### Phase 3: Testing (Week 3)
- [ ] Test with small group of users
- [ ] Monitor delivery rates
- [ ] Check conversation costs
- [ ] Test failover to email
- [ ] Load testing with queue workers

### Phase 4: Production (Week 4)
- [ ] Deploy to production
- [ ] Monitor first 24 hours closely
- [ ] Train staff on managing failed jobs
- [ ] Document troubleshooting procedures
- [ ] Set up alerts for failures

---

## 10. Troubleshooting

### Common Issues

**Messages not delivered:**
- Check phone has WhatsApp installed
- Verify phone number format (+263...)
- Check Meta Business Manager for restrictions
- Ensure number is in allowed list (dev mode)

**Access token expired:**
- Generate long-lived token
- Implement auto-refresh mechanism
- Monitor token expiry

**Queue jobs stuck:**
- Restart queue workers
- Check Redis connection
- Review failed_jobs table

**Rate limit exceeded:**
- Implement throttling
- Use queue batching
- Upgrade Meta tier

---

## 11. Support Resources

- **Meta WhatsApp Business API Docs:** https://developers.facebook.com/docs/whatsapp
- **Meta Business Manager:** https://business.facebook.com
- **Laravel Queue Docs:** https://laravel.com/docs/queues
- **Support:** Meta Business Support (via Business Manager)

---

## Summary: Recommended Production Setup

```env
# Production .env
WHATSAPP_PROVIDER=meta
WHATSAPP_USE_TEMPLATES=true
META_WHATSAPP_TOKEN=<long-lived-token>
META_WHATSAPP_PHONE_ID=<verified-phone-id>
META_WHATSAPP_BUSINESS_ID=<business-id>

QUEUE_CONNECTION=redis
REDIS_HOST=127.0.0.1

LOG_CHANNEL=stack
```

**Queue Worker (systemd service):**
- 2-3 workers
- Redis queue
- Auto-restart on failure

**Templates:**
- All notification types approved
- Branded with company name
- Professional formatting

**Monitoring:**
- Dedicated WhatsApp logs
- Failed job alerts
- Delivery rate tracking
- Cost monitoring

**Backup:**
- Email fallback enabled
- Database notifications always logged
- SMS for critical alerts (optional)