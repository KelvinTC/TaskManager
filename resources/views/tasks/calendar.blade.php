@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-8">
            <h2 class="mb-1"><i class="bi bi-calendar3"></i> Task Calendar</h2>
            <p class="text-muted">View and manage your scheduled tasks</p>
        </div>
        <div class="col-md-4 text-end">
            <a href="{{ route('tasks.index') }}" class="btn btn-secondary">
                <i class="bi bi-list-task"></i> List View
            </a>
            @if(Auth::user()->canManageTasks())
                <a href="{{ route('tasks.create') }}" class="btn btn-success">
                    <i class="bi bi-plus-circle"></i> New Task
                </a>
            @endif
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <div id="calendar"></div>
        </div>
    </div>

    <!-- Task Detail Modal -->
    <div class="modal fade" id="taskModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="taskModalLabel">Task Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="taskModalBody">
                    <p class="text-center"><i class="bi bi-hourglass-split"></i> Loading...</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <a href="#" id="viewTaskBtn" class="btn btn-primary">View Full Details</a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- FullCalendar CSS -->
<link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.css" rel="stylesheet">

<!-- FullCalendar JS -->
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const calendarEl = document.getElementById('calendar');
    const calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek,timeGridDay,listWeek'
        },
        events: '{{ route('tasks.json') }}',
        eventClick: function(info) {
            info.jsEvent.preventDefault();

            const taskId = info.event.id;
            const taskTitle = info.event.title;
            const taskStatus = info.event.extendedProps.status;
            const taskPriority = info.event.extendedProps.priority;
            const taskStart = info.event.start;

            // Update modal content
            document.getElementById('taskModalLabel').textContent = taskTitle;
            document.getElementById('taskModalBody').innerHTML = `
                <div class="mb-3">
                    <strong>Status:</strong>
                    <span class="badge bg-${getStatusColor(taskStatus)}">${formatStatus(taskStatus)}</span>
                </div>
                <div class="mb-3">
                    <strong>Priority:</strong>
                    <span class="badge bg-${getPriorityColor(taskPriority)}">${capitalize(taskPriority)}</span>
                </div>
                <div class="mb-3">
                    <strong>Scheduled:</strong> ${formatDate(taskStart)}
                </div>
            `;
            document.getElementById('viewTaskBtn').href = '/tasks/' + taskId;

            // Show modal
            const modal = new bootstrap.Modal(document.getElementById('taskModal'));
            modal.show();
        },
        eventDidMount: function(info) {
            info.el.style.cursor = 'pointer';
        },
        height: 'auto',
        buttonText: {
            today: 'Today',
            month: 'Month',
            week: 'Week',
            day: 'Day',
            list: 'List'
        }
    });

    calendar.render();

    function getStatusColor(status) {
        const colors = {
            'pending': 'warning',
            'in_progress': 'info',
            'completed': 'success',
            'overdue': 'danger'
        };
        return colors[status] || 'secondary';
    }

    function getPriorityColor(priority) {
        const colors = {
            'low': 'secondary',
            'medium': 'warning',
            'high': 'danger'
        };
        return colors[priority] || 'secondary';
    }

    function formatStatus(status) {
        return status.replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase());
    }

    function capitalize(str) {
        return str.charAt(0).toUpperCase() + str.slice(1);
    }

    function formatDate(date) {
        return new Date(date).toLocaleString('en-US', {
            weekday: 'short',
            year: 'numeric',
            month: 'short',
            day: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        });
    }
});
</script>

<style>
#calendar {
    max-width: 100%;
    margin: 0 auto;
}

.fc-event {
    cursor: pointer;
}

.fc-daygrid-event {
    white-space: normal;
}
</style>
@endsection
