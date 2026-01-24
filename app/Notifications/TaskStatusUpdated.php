<?php

namespace App\Notifications;

use App\Models\Task;
use App\Channels\SmsChannel;
use App\Channels\WhatsappChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TaskStatusUpdated extends Notification
{
    use Queueable;

    protected $task;

    public function __construct(Task $task)
    {
        $this->task = $task;
    }

    public function via($notifiable)
    {
        $channels = ['database'];

        switch ($notifiable->preferred_channel) {
            case 'sms':
                $channels[] = SmsChannel::class;
                break;
            case 'whatsapp':
                $channels[] = WhatsappChannel::class;
                break;
            case 'email':
                $channels[] = 'mail';
                break;
        }

        return $channels;
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->from(
                config('mail.notification.address', config('mail.from.address')),
                config('mail.notification.name', config('mail.from.name'))
            )
            ->subject('Task Status Updated: ' . $this->task->title)
            ->greeting('Hello ' . $notifiable->name . '!')
            ->line('The status of a task has been updated.')
            ->line('Task: ' . $this->task->title)
            ->line('New Status: ' . ucfirst(str_replace('_', ' ', $this->task->status)))
            ->action('View Task', url('/tasks/' . $this->task->id))
            ->line('Thank you!');
    }

    public function toDatabase($notifiable)
    {
        return [
            'task_id' => $this->task->id,
            'task_title' => $this->task->title,
            'message' => 'Task "' . $this->task->title . '" status updated to ' . $this->task->status,
            'status' => $this->task->status,
        ];
    }

    public function toSms($notifiable)
    {
        return 'Task Status Updated: ' . $this->task->title .
               ' - New status: ' . ucfirst(str_replace('_', ' ', $this->task->status));
    }

    public function toWhatsapp($notifiable)
    {
        $taskUrl = url('/tasks/' . $this->task->id);
        $status = ucfirst(str_replace('_', ' ', $this->task->status));

        // Status emojis
        $statusEmoji = match($this->task->status) {
            'pending' => '⏳',
            'in_progress' => '⚙️',
            'completed' => '✅',
            'cancelled' => '❌',
            default => '📋'
        };

        return "{$statusEmoji} *Task Status Updated*\n\n" .
               "*{$this->task->title}*\n\n" .
               "📊 *New Status:* {$status}\n\n" .
               "👉 View task:\n{$taskUrl}";
    }
}
