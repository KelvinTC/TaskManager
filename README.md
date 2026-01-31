# Task Scheduling & Notification System

A comprehensive Laravel-based task management system with Vue.js frontend, supporting task assignment, scheduling, reporting, SMS/WhatsApp notifications, and in-app notifications.

## Features

### Core Features
- ✅ **Task Management**: Create, assign, update, and track tasks
- ✅ **User Roles**: Client (assigns tasks) and Worker (completes tasks)
- ✅ **Task Scheduling**: Schedule tasks with due dates and priorities
- ✅ **Task Rescheduling**: Flexible rescheduling with automatic notifications
- ✅ **Status Tracking**: Pending → In Progress → Completed → Overdue workflow

### Notification System
- ✅ **Multi-Channel Notifications**:
  - In-app notifications (database)
  - Email notifications
  - SMS notifications (via Twilio)
  - WhatsApp notifications (via Twilio)
- ✅ **User Preferences**: Users can choose their preferred notification channel
- ✅ **Notification Triggers**:
  - Task assignment
  - Task rescheduled
  - Task status updated
  - Task overdue
  - Task due soon

### Reporting & Analytics
- ✅ **Worker Performance Reports**:
  - Total tasks
  - Completed tasks
  - Pending tasks
  - Overdue tasks
  - Average completion timeclau
- ✅ **Time-Based Reports**: Daily, weekly, and monthly reports
- ✅ **Client Dashboard**: Overview of all tasks and worker activity
- ✅ **Worker Dashboard**: Personal task overview and upcoming tasks

### Automation
- ✅ **Scheduled Jobs**: Hourly check for overdue tasks
- ✅ **Automatic Notifications**: Automated alerts for overdue tasks
- ✅ **Queue Support**: Background job processing for notifications

### API Support
- ✅ **RESTful API**: Complete API for mobile app integration
- ✅ **Laravel Sanctum**: Token-based authentication
- ✅ **API Endpoints**:
  - GET/POST/PATCH/DELETE `/api/tasks`
  - PATCH `/api/tasks/{id}/reschedule`
  - GET `/api/workers/{id}/report`

## Technology Stack

### Backend
- **Framework**: Laravel 12
- **Authentication**: Laravel UI + Sanctum
- **Database**: MySQL/PostgreSQL/SQLite
- **Queue**: Database/Redis
- **Notifications**: Twilio (SMS/WhatsApp)

### Frontend
- **Framework**: Vue.js 3
- **UI**: Bootstrap 5
- **Build Tool**: Vite
- **Calendar**: FullCalendar (ready to integrate)

## Installation

### Prerequisites
- PHP 8.2 or higher
- Composer
- Node.js 18+ and NPM
- MySQL/PostgreSQL (or SQLite for development)
- Twilio account (for SMS/WhatsApp)

### Step 1: Install Dependencies
```bash
# Install PHP dependencies
composer install

# Install JavaScript dependencies
npm install --legacy-peer-deps
```

### Step 2: Environment Configuration
```bash
# Copy environment file
cp .env.example .env

# Generate application key
php artisan key:generate
```

### Step 3: Database Configuration
Edit `.env` file and configure your database:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=task_scheduling
DB_USERNAME=root
DB_PASSWORD=
```

### Step 4: Twilio Configuration
Add your Twilio credentials to `.env`:

```env
TWILIO_SID=your_twilio_account_sid
TWILIO_TOKEN=your_twilio_auth_token
TWILIO_FROM=+1234567890
TWILIO_WHATSAPP_FROM=whatsapp:+14155238886
```

### Step 5: Run Migrations
```bash
php artisan migrate
```

### Step 6: Build Frontend Assets
```bash
npm run build
# Or for development
npm run dev
```

### Step 7: Start the Application
```bash
php artisan serve
```

Visit `http://localhost:8000` in your browser.

## Configuration

### Queue Configuration
For background job processing, configure your queue connection in `.env`:

```env
QUEUE_CONNECTION=database
```

Run the queue worker:
```bash
php artisan queue:work
```

### Scheduler Configuration
Add this to your cron tab to run the scheduler:

```bash
* * * * * cd /path-to-your-project && php artisan schedule:run >> /dev/null 2>&1
```

## User Roles

### Client
- Create and assign tasks to workers
- Reschedule tasks
- Monitor task progress
- View reporting and analytics
- Delete tasks

### Worker
- Receive assigned tasks
- Update task status
- Reschedule tasks (optional)
- View personal task calendar
- Receive notifications (in-app, SMS, WhatsApp, email)

## Database Structure

### Users Table
- id, name, email, phone
- role (client/worker)
- preferred_channel (sms/whatsapp/email/in_app)
- timestamps

### Tasks Table
- id, title, description
- assigned_to (Worker ID)
- created_by (Client ID)
- status (pending/in_progress/completed/overdue)
- scheduled_at, completed_at
- priority (low/medium/high)
- timestamps

## API Documentation

### Task Endpoints

#### Get All Tasks
```bash
GET /api/tasks
Authorization: Bearer {token}
```

#### Create Task
```bash
POST /api/tasks
Authorization: Bearer {token}
Content-Type: application/json

{
    "title": "Task Title",
    "description": "Task Description",
    "assigned_to": 1,
    "scheduled_at": "2025-12-10 14:00:00",
    "priority": "high"
}
```

#### Update Task
```bash
PATCH /api/tasks/{id}
Authorization: Bearer {token}
Content-Type: application/json

{
    "status": "in_progress"
}
```

#### Reschedule Task
```bash
PATCH /api/tasks/{id}/reschedule
Authorization: Bearer {token}
Content-Type: application/json

{
    "scheduled_at": "2025-12-11 10:00:00"
}
```

#### Worker Performance Report
```bash
GET /api/workers/{id}/report
Authorization: Bearer {token}
```

## Routes

### Web Routes
- `/dashboard` - Main dashboard (role-based)
- `/tasks` - Task management
- `/calendar` - Calendar view
- `/reports` - Reports index

### API Routes
- `GET /api/tasks` - List all tasks
- `POST /api/tasks` - Create new task
- `PATCH /api/tasks/{id}` - Update task
- `DELETE /api/tasks/{id}` - Delete task
- `PATCH /api/tasks/{id}/reschedule` - Reschedule task

## Scheduled Jobs

### Check Overdue Tasks
Runs hourly to check for overdue tasks and send notifications.

```php
Schedule::job(new CheckOverdueTasks())->hourly();
```

## Development

### Running Tests
```bash
php artisan test
```

### Code Style
```bash
./vendor/bin/pint
```

### Building for Production
```bash
npm run build
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

## License

This project is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
