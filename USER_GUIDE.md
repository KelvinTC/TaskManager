# Task Management System - User Guide

## Table of Contents
1. [Getting Started](#getting-started)
2. [Admin User Guide](#admin-user-guide)
3. [Employee User Guide](#employee-user-guide)
4. [Common Tasks](#common-tasks)
5. [Troubleshooting](#troubleshooting)
6. [FAQ](#faq)

---

# Getting Started

## Accessing the System

1. Open your web browser (Chrome, Firefox, Safari, or Edge recommended)
2. Navigate to your organization's task management URL
3. You will see the login page

## First Time Login

### For New Users (Invited)
1. Check your email for the invitation
2. Click the registration link in the email
3. Fill in your details:
   - Name
   - Email (must match the invited email)
   - Password
   - Confirm Password
4. Click **"Register"**
5. You'll be automatically logged in

### For Existing Users
1. Enter your email address
2. Enter your password
3. Click **"Login"**
4. Optional: Check "Remember Me" to stay logged in

## Password Reset
If you forgot your password:
1. Click **"Forgot Password?"** on the login page
2. Enter your email address
3. Click **"Send Password Reset Link"**
4. Check your email for the reset link
5. Click the link and enter your new password
6. Confirm the new password
7. Click **"Reset Password"**

---

# Admin User Guide

## Dashboard Overview

When you log in as an admin, you'll see the Admin Dashboard with:

### Quick Statistics (Top of Page)
- **Total Tasks**: All tasks in the system
- **Active**: Tasks that are pending or in progress
- **Completed**: Successfully finished tasks
- **Overdue**: Tasks past their scheduled time

### Navigation Tabs
1. **Team Performance**: View all employees and their statistics
2. **Recent Tasks**: Latest tasks in the system
3. **Reports & Filters**: Advanced filtering and export options

---

## Managing Users

### Inviting a New User

1. Click **"Users"** in the navigation menu
2. Click **"Invite New User"** button
3. Fill in the invitation form:
   - **Email Address**: User's email (required)
   - **Phone Number (WhatsApp)**: For WhatsApp notifications (required)
   - **Role**: Select Admin or Employee
4. Click **"Send Invitation"**
5. The user will receive an email invitation
6. If phone number provided, they'll also get a WhatsApp message

**Note**: Users must register using the exact email address you invited.

### Viewing All Users

1. Click **"Users"** in the navigation menu
2. You'll see a list of all users with:
   - Name
   - Email
   - Role
   - Registration status

---

## Managing Tasks

### Creating a New Task

1. Click **"New Task"** button (green button on dashboard or navigation)
2. Fill in the task form:

   **Required Fields**:
   - **Title**: Brief description (e.g., "Fix broken AC unit")
   - **Assigned To**: Select an employee from dropdown
   - **Scheduled Date & Time**: When task should be completed
   - **Priority**: Choose Low, Medium, or High

   **Optional Fields**:
   - **Description**: Detailed instructions or notes

3. Click **"Create Task"** button
4. The assigned employee will receive:
   - Email notification
   - WhatsApp notification (if phone number is registered)
   - In-app notification

**Best Practices**:
- Use clear, specific titles
- Set realistic scheduled times
- Include detailed instructions in description
- Assign high priority only when truly urgent

### Viewing All Tasks

1. Click **"Tasks"** in the navigation menu
2. You'll see a paginated list of all tasks you've created
3. Each task shows:
   - Title
   - Assigned employee
   - Status badge (color-coded)
   - Priority badge
   - Scheduled date/time
   - Created date

4. Click on any task row to view full details

### Viewing Task Details

1. Click on a task from the task list
2. You'll see complete information:
   - Full title and description
   - Created by (you)
   - Assigned to (employee name and email)
   - Status and priority
   - Scheduled date and time
   - When it was created

3. Available Actions:
   - **Edit Task**: Modify any task details
   - **Delete Task**: Remove the task permanently

### Editing a Task

1. Open the task details page
2. Click **"Edit Task"** button (yellow button)
3. Modify any fields:
   - Title
   - Description
   - Assigned employee
   - Scheduled date/time
   - Priority
   - Status (Pending, In Progress, Completed)
4. Click **"Update Task"**

**Important**:
- If you change the scheduled time, both you and the employee get notified
- If you change the assigned employee, the new employee gets notified

### Deleting a Task

1. Open the task details page
2. Click **"Delete Task"** button (red button)
3. Confirm the deletion in the popup
4. Task is permanently removed

**Warning**: This action cannot be undone!

### Filtering Tasks

1. Go to Dashboard → **Reports & Filters** tab
2. Use the filter dropdowns:
   - **Status**: All, Pending, In Progress, Completed
   - **Priority**: All, Low, Medium, High
   - **Employee**: All or specific employee
3. Click **"Apply Filters"**
4. Results appear in the task list

---

## Using the Calendar

### Viewing Tasks on Calendar

1. Click **"Calendar"** in the navigation menu
2. You'll see all your tasks displayed on a calendar

**Calendar Views**:
- **Month**: Click "Month" button
- **Week**: Click "Week" button
- **Day**: Click "Day" button
- **List**: Click "List" button

**Navigation**:
- **Previous**: Arrow left button
- **Next**: Arrow right button
- **Today**: Jump to current date

### Understanding Calendar Colors

Tasks are color-coded by status:
- **Yellow**: Pending tasks
- **Blue**: In Progress tasks
- **Green**: Completed tasks
- **Red**: Overdue tasks

### Viewing Task Details from Calendar

1. Click on any task event in the calendar
2. A popup shows:
   - Task title
   - Status
   - Priority
   - Scheduled date/time
3. Click **"View Full Details"** to see complete task info

---

## Team Performance Monitoring

### Viewing Employee Performance

1. Go to Dashboard → **Team Performance** tab
2. View the performance table with columns:
   - **Employee**: Name
   - **Total**: Total tasks assigned
   - **Completed**: Tasks finished
   - **Active**: Tasks in progress or pending
   - **Overdue**: Tasks past deadline
   - **Completion Rate**: Visual progress bar with percentage
   - **Rating**: Performance badge

**Rating System**:
- ⭐ **Excellent**: 75% or higher completion rate
- 👍 **Good**: 50-74% completion rate
- 📈 **Growing**: Below 50% completion rate

### Using Performance Data

- Identify top performers
- Find employees who need support
- Balance workload distribution
- Recognize and reward high achievers
- Provide training for struggling employees

---

## Reports & Export

### Exporting CSV Reports

1. Go to Dashboard → **Reports & Filters** tab
2. Click **"Download CSV Report"** button
3. A CSV file will download with:
   - Employee names and emails
   - Task statistics
   - Completion rates
   - Summary statistics
   - Report generation timestamp

4. Open in Excel, Google Sheets, or any spreadsheet software

**CSV Contents**:
- All employee performance data
- Total tasks per employee
- Completed, active, and overdue counts
- Overall system statistics
- Generated date for record keeping

### Printing Dashboard

1. Go to Dashboard
2. Scroll to **Reports & Filters** tab
3. Click **"Print Dashboard"**
4. Your browser's print dialog opens
5. Select printer or "Save as PDF"
6. Click **"Print"**

---

## Notifications

### Email Notifications
You receive emails when:
- An employee updates a task status
- A task is rescheduled by an employee (future feature)
- System announcements

### In-App Notifications
1. Look for the notification bell icon (if implemented)
2. Click to view all notifications
3. Click on a notification to see details
4. Mark as read or clear notifications

---

# Employee User Guide

## Dashboard Overview

When you log in as an employee, you'll see:

### Quick Statistics
- **Total Tasks**: All tasks assigned to you
- **Active**: Your pending and in-progress tasks
- **Completed**: Your finished tasks
- **Overdue**: Your tasks past deadline

### Your Task List
- All tasks assigned to you
- Sorted by most recent
- Color-coded status badges
- Quick access to task details

---

## Viewing Your Tasks

### Task List View

1. Your dashboard shows all assigned tasks
2. Or click **"Tasks"** in navigation menu
3. Each task displays:
   - Title
   - Status (Pending, In Progress, Completed)
   - Priority (Low, Medium, High)
   - Scheduled date and time
   - Created date

### Task Details

1. Click on any task to view full details
2. You'll see:
   - Complete description
   - Who assigned it (admin name)
   - Your name as assignee
   - Scheduled date and time
   - When it was created
   - Current status

---

## Updating Task Status

### How to Update Status

1. Open a task assigned to you
2. Scroll down to the **"Update Task Status"** section (blue card)
3. Select new status from dropdown:
   - **Pending**: Not started yet
   - **In Progress**: Currently working on it
   - **Completed**: Finished the task
4. Click the green **"Update"** button

**What Happens Next**:
- Your admin receives an email notification
- Your admin gets a WhatsApp notification (if configured)
- Task status updates across the system
- If marked completed, completion timestamp is recorded

### Status Change Guidelines

**When to use "Pending"**:
- Task not yet started
- Waiting for resources or information
- Scheduled for future date

**When to use "In Progress"**:
- Actively working on the task
- Task has been started
- Making progress but not complete

**When to use "Completed"**:
- Task fully finished
- All requirements met
- Ready for review (if applicable)

---

## Using Your Calendar

### Viewing Your Schedule

1. Click **"Calendar"** in navigation
2. See all your assigned tasks in calendar format

**Same Features as Admin**:
- Multiple views (Month, Week, Day, List)
- Color-coded by status
- Click tasks for quick details
- Navigate between dates

**Use Calendar To**:
- Plan your day/week
- See upcoming deadlines
- Identify overdue tasks
- Prioritize work

---

## Notifications

### What You'll Be Notified About

**Email Notifications**:
- New task assigned to you
- Task rescheduled by admin
- Task details changed by admin
- Important system announcements

**WhatsApp Notifications** (if phone number registered):
- New task assignments
- Status updates confirmed
- Deadline reminders
- Urgent task alerts

**In-App Notifications**:
- Real-time updates
- Click to view details
- Stay informed while using the system

### Managing Notifications

- Check email regularly for new assignments
- Ensure WhatsApp notifications are enabled
- Update your phone number if it changes
- Contact admin if not receiving notifications

---

# Common Tasks

## Changing Your Password

1. Click on your name (top right corner)
2. Select **"Profile"** or **"Settings"**
3. Click **"Change Password"**
4. Enter:
   - Current password
   - New password
   - Confirm new password
5. Click **"Update Password"**

---

## Updating Your Profile

1. Click on your name (top right)
2. Select **"Profile"**
3. Update your information:
   - Name
   - Email
   - Phone number
4. Click **"Save Changes"**

---

## Logging Out

1. Click your name in the top right corner
2. Click **"Logout"**
3. You'll be returned to the login page

**Security Tip**: Always logout when using a shared computer!

---

# Troubleshooting

## Cannot Login

**Problem**: Email or password not working

**Solutions**:
1. Check that Caps Lock is OFF
2. Verify you're using the correct email
3. Try the "Forgot Password" option
4. Clear your browser cache and cookies
5. Try a different browser
6. Contact your admin

---

## Not Receiving Notifications

**Problem**: Not getting email or WhatsApp notifications

**Solutions**:

**For Email**:
1. Check your spam/junk folder
2. Add the system email to your contacts
3. Verify your email address in profile
4. Contact your admin

**For WhatsApp**:
1. Verify your phone number is registered
2. Check that WhatsApp is installed
3. Ensure WhatsApp notifications are enabled
4. Verify number includes country code
5. Contact your admin

---

## Task Not Showing

**Problem**: Cannot see a task that should be assigned to you

**Solutions**:
1. Refresh the page (F5 or Ctrl+R)
2. Check different tabs on dashboard
3. Use calendar view
4. Check if task was reassigned
5. Contact the admin who created it

---

## Cannot Update Task Status (Employee)

**Problem**: Update button not working or missing

**Solutions**:
1. Verify task is assigned to you
2. Refresh the page
3. Check if you're logged in with correct account
4. Ensure you have employee role
5. Contact admin

---

## Cannot Create Task (Admin)

**Problem**: Create task button missing or not working

**Solutions**:
1. Verify you have admin role
2. Check that employees exist in system
3. Refresh the page
4. Clear browser cache
5. Try different browser
6. Contact system administrator

---

## Page Loading Slowly

**Problem**: System is slow or unresponsive

**Solutions**:
1. Check your internet connection
2. Close unnecessary browser tabs
3. Clear browser cache
4. Restart your browser
5. Try incognito/private mode
6. Use a different browser
7. Contact system administrator if persistent

---

## Calendar Not Displaying

**Problem**: Calendar view is blank or showing errors

**Solutions**:
1. Refresh the page
2. Check if you have any tasks scheduled
3. Clear browser cache
4. Disable browser extensions temporarily
5. Try a different browser
6. Contact system administrator

---

# FAQ

## General Questions

### Q: What browsers are supported?
**A**: Chrome, Firefox, Safari, and Edge (latest versions recommended)

### Q: Can I use this on my mobile phone?
**A**: Yes! The system is fully responsive and works on smartphones and tablets.

### Q: How long do I stay logged in?
**A**: Sessions last 2 hours of inactivity. Use "Remember Me" at login for longer sessions.

### Q: Can I access the system from home?
**A**: Yes, as long as you have internet access and the system URL.

---

## For Admins

### Q: How many users can I invite?
**A**: There's no limit. Invite as many admins and employees as needed.

### Q: Can I assign one task to multiple employees?
**A**: No, each task is assigned to one employee. Create separate tasks for multiple people.

### Q: Can I see deleted tasks?
**A**: No, deleted tasks are permanently removed. Be careful when deleting.

### Q: How do I export reports?
**A**: Go to Dashboard → Reports & Filters tab → Click "Download CSV Report"

### Q: Can I change an employee to admin?
**A**: Contact your system administrator for role changes.

### Q: What happens if I reschedule a task?
**A**: Both you and the assigned employee receive notifications about the change.

### Q: Can I assign tasks to myself?
**A**: Yes, if you're also registered as an employee, you can assign tasks to yourself.

---

## For Employees

### Q: Can I create tasks?
**A**: No, only admins can create tasks. You can only update status of assigned tasks.

### Q: Can I delete tasks assigned to me?
**A**: No, only the admin who created the task can delete it.

### Q: What if I can't complete a task on time?
**A**: Update the status to "In Progress" and contact your admin about the delay.

### Q: Can I see tasks assigned to other employees?
**A**: No, you only see tasks assigned to you.

### Q: Can I reassign a task to someone else?
**A**: No, contact your admin to reassign the task.

### Q: What does "Overdue" mean?
**A**: The scheduled time has passed and the task isn't marked completed.

---

## Technical Questions

### Q: Is my data secure?
**A**: Yes, the system uses encryption, secure sessions, and role-based access control.

### Q: What happens if I lose internet connection?
**A**: You'll need internet to access the system. Reconnect and refresh the page.

### Q: Can I use keyboard shortcuts?
**A**: Standard browser shortcuts work (Ctrl+F to find, etc.). No custom shortcuts currently.

### Q: How often is data backed up?
**A**: Contact your system administrator for backup policies.

### Q: Can I export my task history?
**A**: Admins can export all data via CSV. Employees should request reports from their admin.

---

## Getting Help

### For Technical Issues
1. Try the troubleshooting section above
2. Contact your system administrator
3. Provide details: what you were doing, error messages, browser used

### For Questions About Tasks
1. Contact the admin who created the task
2. Check task description for instructions
3. Use in-app communication (if available)

### For Account Issues
1. Contact your organization's admin
2. Use "Forgot Password" for password issues
3. Verify you're using the correct email address

---

## Tips for Success

### For Admins
✅ Create clear, detailed task descriptions
✅ Set realistic deadlines
✅ Monitor team performance regularly
✅ Use priority levels appropriately
✅ Export reports for record keeping
✅ Provide feedback to employees
✅ Keep user information updated

### For Employees
✅ Check dashboard daily
✅ Update task status promptly
✅ Mark tasks complete as soon as finished
✅ Use calendar view to plan your day
✅ Contact admin if you need help
✅ Keep your profile information current
✅ Enable notifications for real-time updates

---

## Quick Reference Card

### Admin Quick Actions
| Action | Steps |
|--------|-------|
| Create Task | Dashboard → New Task → Fill form → Create |
| View Team Performance | Dashboard → Team Performance tab |
| Export Report | Dashboard → Reports & Filters → Download CSV |
| Invite User | Users → Invite New User → Fill form → Send |
| Edit Task | Tasks → Click task → Edit Task button |

### Employee Quick Actions
| Action | Steps |
|--------|-------|
| Update Status | Tasks → Click task → Select status → Update |
| View Schedule | Calendar → Choose view |
| Check Tasks | Dashboard or Tasks menu |
| See Task Details | Click on any task |

---

## Contact Support

For additional help or feature requests:
- **Email**: [Your support email]
- **Phone**: [Your support phone]
- **Hours**: [Your support hours]

---

**Version**: 1.0
**Last Updated**: January 2026
**System**: SmartWork Task Management System

---

**Remember**: This guide is your reference. Bookmark it for quick access!
