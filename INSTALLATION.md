# Quick Installation Guide

## Prerequisites
- PHP 8.2+
- Composer
- Node.js 18+ & NPM
- MySQL/PostgreSQL
- Twilio Account

## Quick Start

### 1. Install Dependencies
```bash
composer install
npm install --legacy-peer-deps
```

### 2. Configure Environment
```bash
cp .env.example .env
php artisan key:generate
```

### 3. Database Setup
Edit `.env`:
```env
DB_CONNECTION=mysql
DB_DATABASE=task_scheduling
DB_USERNAME=root
DB_PASSWORD=your_password
```

### 4. Twilio Setup (Optional)
Edit `.env`:
```env
TWILIO_SID=your_sid
TWILIO_TOKEN=your_token
TWILIO_FROM=your_phone_number
TWILIO_WHATSAPP_FROM=whatsapp:+14155238886
```

### 5. Run Migrations
```bash
php artisan migrate
```

### 6. Build Frontend
```bash
npm run build
```

### 7. Start Server
```bash
php artisan serve
```

Visit `http://localhost:8000`

## Testing

Create test users via registration:
- **Client**: Can create and assign tasks
- **Worker**: Can receive and update tasks

## Queue Worker (Optional)
```bash
php artisan queue:work
```

## Scheduler (Optional)
Add to crontab:
```bash
* * * * * cd /path-to-project && php artisan schedule:run >> /dev/null 2>&1
```

## Next Steps

1. Register a client account
2. Register a worker account
3. Create your first task
4. Test notifications

For full documentation, see [README.md](README.md)
