<?php

namespace App\Notifications;

use App\Models\Task;
use App\Channels\SmsChannel;
use App\Channels\WhatsappChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TaskReminder extends Notification
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
        $timeUntilDue = $this->task->scheduled_at->diffForHumans();

        return (new MailMessage)
            ->from(
                config('mail.notification.address', config('mail.from.address')),
                config('mail.notification.name', config('mail.from.name'))
            )
            ->subject('Task Reminder: ' . $this->task->title)
            ->greeting('Hello ' . $notifiable->name . '!')
            ->line('This is a reminder about an upcoming task.')
            ->line('Task: ' . $this->task->title)
            ->line('Scheduled for: ' . $this->task->scheduled_at->format('M d, Y H:i'))
            ->line('Time until due: ' . $timeUntilDue)
            ->line('Priority: ' . ucfirst($this->task->priority))
            ->action('View Task', url('/tasks/' . $this->task->id))
            ->line('Please ensure this task is completed on time.');
    }

    public function toDatabase($notifiable)
    {
        return [
            'task_id' => $this->task->id,
            'task_title' => $this->task->title,
            'message' => 'Reminder: Task "' . $this->task->title . '" is due soon!',
            'scheduled_at' => $this->task->scheduled_at,
            'priority' => $this->task->priority,
        ];
    }

    public function toWhatsapp($notifiable)
    {
        $taskUrl = url('/tasks/' . $this->task->id);
        $priorityEmoji = $this->task->priority === 'high' ? '🔴' : ($this->task->priority === 'medium' ? '🟡' : '🟢');
        $timeUntilDue = $this->task->scheduled_at->diffForHumans();

        return "⏰ *TASK REMINDER*\n\n" .
               "*{$this->task->title}*\n\n" .
               "📅 *Scheduled:* " . $this->task->scheduled_at->format('M d, Y \a\t H:i') . "\n" .
               "⏱️ *Due:* {$timeUntilDue}\n" .
               "{$priorityEmoji} *Priority:* " . ucfirst($this->task->priority) . "\n\n" .
               "📝 *Description:* " . ($this->task->description ? \Illuminate\Support\Str::limit($this->task->description, 100) : 'No description') . "\n\n" .
               "✅ Please complete this task on time!\n\n" .
               "👉 View task:\n{$taskUrl}";
    }
}
