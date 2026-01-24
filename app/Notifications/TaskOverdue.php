<?php

namespace App\Notifications;

use App\Models\Task;
use App\Channels\SmsChannel;
use App\Channels\WhatsappChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TaskOverdue extends Notification implements ShouldQueue
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
            ->subject('Task Overdue: ' . $this->task->title)
            ->greeting('Hello ' . $notifiable->name . '!')
            ->line('A task is now overdue!')
            ->line('Task: ' . $this->task->title)
            ->line('Was scheduled for: ' . $this->task->scheduled_at->format('M d, Y H:i'))
            ->line('Priority: ' . ucfirst($this->task->priority))
            ->action('View Task', url('/tasks/' . $this->task->id))
            ->line('Please address this task as soon as possible.');
    }

    public function toDatabase($notifiable)
    {
        return [
            'task_id' => $this->task->id,
            'task_title' => $this->task->title,
            'message' => 'Task "' . $this->task->title . '" is now overdue!',
            'scheduled_at' => $this->task->scheduled_at,
            'priority' => $this->task->priority,
        ];
    }

    public function toSms($notifiable)
    {
        return 'OVERDUE: ' . $this->task->title .
               ' was due ' . $this->task->scheduled_at->format('M d, Y H:i') .
               '. Please complete ASAP.';
    }

    public function toWhatsapp($notifiable)
    {
        $taskUrl = url('/tasks/' . $this->task->id);
        $priorityEmoji = $this->task->priority === 'high' ? '🔴' : ($this->task->priority === 'medium' ? '🟡' : '🟢');

        return "🚨 *TASK OVERDUE!*\n\n" .
               "*{$this->task->title}*\n\n" .
               "⏰ *Was due:* " . $this->task->scheduled_at->format('M d, Y \a\t H:i') . "\n" .
               "{$priorityEmoji} *Priority:* " . ucfirst($this->task->priority) . "\n\n" .
               "⚠️ *Action Required:* Please complete this task as soon as possible!\n\n" .
               "👉 View task:\n{$taskUrl}";
    }
}
