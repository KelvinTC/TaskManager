<?php

namespace App\Notifications;

use App\Models\Task;
use App\Channels\SmsChannel;
use App\Channels\WhatsappChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TaskRescheduled extends Notification implements ShouldQueue
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
                config('mail.task.address', config('mail.from.address')),
                config('mail.task.name', config('mail.from.name'))
            )
            ->subject('Task Rescheduled: ' . $this->task->title)
            ->greeting('Hello ' . $notifiable->name . '!')
            ->line('A task has been rescheduled.')
            ->line('Task: ' . $this->task->title)
            ->line('New Schedule: ' . $this->task->scheduled_at->format('M d, Y H:i'))
            ->action('View Task', url('/tasks/' . $this->task->id))
            ->line('Please take note of the updated schedule.');
    }

    public function toDatabase($notifiable)
    {
        return [
            'task_id' => $this->task->id,
            'task_title' => $this->task->title,
            'message' => 'Task "' . $this->task->title . '" has been rescheduled.',
            'scheduled_at' => $this->task->scheduled_at,
        ];
    }

    public function toSms($notifiable)
    {
        return 'Task Rescheduled: ' . $this->task->title .
               ' - New date: ' . $this->task->scheduled_at->format('M d, Y H:i');
    }

    public function toWhatsapp($notifiable)
    {
        $taskUrl = url('/tasks/' . $this->task->id);
        $priorityEmoji = $this->task->priority === 'high' ? '🔴' : ($this->task->priority === 'medium' ? '🟡' : '🟢');

        return "🔄 *Task Rescheduled*\n\n" .
               "*{$this->task->title}*\n\n" .
               "📅 *New Schedule:* " . $this->task->scheduled_at->format('M d, Y \a\t H:i') . "\n" .
               "{$priorityEmoji} *Priority:* " . ucfirst($this->task->priority) . "\n\n" .
               "⚠️ Please note the updated schedule!\n\n" .
               "👉 View task:\n{$taskUrl}";
    }

    public function useWhatsappTemplate()
    {
        return config('services.whatsapp.use_templates', false) &&
               config('services.whatsapp.provider') === 'meta';
    }

    public function toWhatsappTemplate($notifiable)
    {
        return [
            'name' => config('services.whatsapp.templates.task_rescheduled', 'hello_world'),
            'language' => 'en_US',
        ];
    }
}
