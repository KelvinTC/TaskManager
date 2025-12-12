# Project Structure

## Overview
This document outlines the structure and organization of the Task Scheduling & Notification System.

## Directory Structure

```
task-scheduling-system/
├── app/
│   ├── Channels/               # Custom notification channels
│   │   ├── SmsChannel.php      # Twilio SMS channel
│   │   └── WhatsappChannel.php # Twilio WhatsApp channel
│   │
│   ├── Console/
│   │   └── Commands/           # Artisan commands
│   │
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Api/            # API controllers
│   │   │   │   ├── TaskApiController.php
│   │   │   │   └── ReportApiController.php
│   │   │   ├── DashboardController.php
│   │   │   ├── TaskController.php
│   │   │   └── ReportController.php
│   │   │
│   │   └── Middleware/
│   │       └── CheckRole.php   # Role-based access control
│   │
│   ├── Jobs/
│   │   └── CheckOverdueTasks.php  # Scheduled job for overdue tasks
│   │
│   ├── Models/
│   │   ├── Task.php            # Task model
│   │   └── User.php            # User model
│   │
│   ├── Notifications/
│   │   ├── TaskAssigned.php    # Task assignment notification
│   │   ├── TaskOverdue.php     # Overdue task notification
│   │   ├── TaskRescheduled.php # Reschedule notification
│   │   └── TaskStatusUpdated.php # Status update notification
│   │
│   └── Policies/
│       └── TaskPolicy.php      # Authorization policies
│
├── config/
│   └── services.php            # Twilio configuration
│
├── database/
│   └── migrations/
│       ├── 0001_01_01_000000_create_users_table.php
│       ├── 2025_12_06_133509_create_tasks_table.php
│       └── [timestamp]_create_notifications_table.php
│
├── resources/
│   ├── js/                     # Vue.js components
│   └── views/                  # Blade templates
│
└── routes/
    ├── api.php                 # API routes
    ├── console.php             # Scheduled tasks
    └── web.php                 # Web routes
```

## Key Components

### Models
- **User**: Manages users (clients and workers)
- **Task**: Manages tasks with assignments and scheduling

### Controllers

#### Web Controllers
- **DashboardController**: Role-based dashboards
- **TaskController**: Task CRUD operations
- **ReportController**: Analytics and reports

#### API Controllers
- **TaskApiController**: RESTful task API
- **ReportApiController**: Performance metrics API

### Notifications
All notifications support multiple channels:
- Database (in-app)
- Email
- SMS (Twilio)
- WhatsApp (Twilio)

### Jobs
- **CheckOverdueTasks**: Runs hourly to mark overdue tasks

### Middleware
- **CheckRole**: Role-based authorization

### Policies
- **TaskPolicy**: Task authorization rules

## Database Schema

### users
```sql
id, name, email, phone, role, preferred_channel, timestamps
```

### tasks
```sql
id, title, description, assigned_to, created_by,
status, scheduled_at, completed_at, priority, timestamps
```

### notifications
```sql
id, type, notifiable_id, notifiable_type, data,
read_at, created_at, updated_at
```

## Workflow

### Task Creation
1. Client creates task via `TaskController@store`
2. Task saved to database
3. `TaskAssigned` notification sent to worker
4. Notification delivered via user's preferred channel

### Task Status Update
1. Worker updates task status
2. `TaskStatusUpdated` notification sent to client
3. If completed, `completed_at` timestamp set

### Overdue Check
1. Scheduler runs hourly (`CheckOverdueTasks`)
2. Finds tasks past `scheduled_at`
3. Updates status to 'overdue'
4. `TaskOverdue` notification sent to both parties

## API Integration

### Authentication
Uses Laravel Sanctum for token-based auth.

### Endpoints
- Tasks: `/api/tasks`
- Reports: `/api/workers/{id}/report`

## Configuration

### Environment Variables
See `.env.example` for all configuration options:
- Database settings
- Twilio credentials
- Queue configuration
- Mail settings

## Development

### Adding New Features
1. Create migration for database changes
2. Update models and relationships
3. Create/update controllers
4. Add routes
5. Create policies if needed
6. Write tests

### Adding New Notifications
1. Create notification class
2. Implement `via()` method for channels
3. Implement channel methods (toMail, toSms, etc.)
4. Dispatch from appropriate controller

## Testing

Run tests:
```bash
php artisan test
```

## Deployment

See [README.md](README.md) for deployment instructions.
