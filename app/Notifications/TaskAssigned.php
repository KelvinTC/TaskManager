<?php

namespace App\Notifications;

use App\Models\Task;
use App\Channels\SmsChannel;
use App\Channels\WhatsappChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TaskAssigned extends Notification implements ShouldQueue
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
            ->subject('New Task Assigned: ' . $this->task->title)
            ->greeting('Hello ' . $notifiable->name . '!')
            ->line('A new task has been assigned to you.')
            ->line('Task: ' . $this->task->title)
            ->line('Priority: ' . ucfirst($this->task->priority))
            ->line('Scheduled for: ' . $this->task->scheduled_at->format('M d, Y H:i'))
            ->action('View Task', url('/tasks/' . $this->task->id))
            ->line('Thank you for your dedication!');
    }

    public function toDatabase($notifiable)
    {
        return [
            'task_id' => $this->task->id,
            'task_title' => $this->task->title,
            'message' => 'A new task "' . $this->task->title . '" has been assigned to you.',
            'scheduled_at' => $this->task->scheduled_at,
            'priority' => $this->task->priority,
        ];
    }

    public function toSms($notifiable)
    {
        return 'New Task: ' . $this->task->title . ' scheduled for ' .
               $this->task->scheduled_at->format('M d, Y H:i') .
               '. Priority: ' . ucfirst($this->task->priority);
    }

    public function toWhatsapp($notifiable)
    {
        $taskUrl = url('/tasks/' . $this->task->id);
        $priorityEmoji = $this->task->priority === 'high' ? '🔴' : ($this->task->priority === 'medium' ? '🟡' : '🟢');

        $message = "📋 *New Task Assigned*\n\n" .
                   "*{$this->task->title}*\n\n";

        if (!empty($this->task->description)) {
            $message .= "📝 " . substr($this->task->description, 0, 100);
            if (strlen($this->task->description) > 100) {
                $message .= "...";
            }
            $message .= "\n\n";
        }

        $message .= "📅 *Scheduled:* " . $this->task->scheduled_at->format('M d, Y \a\t H:i') . "\n" .
                    "{$priorityEmoji} *Priority:* " . ucfirst($this->task->priority) . "\n\n" .
                    "👉 View full details:\n{$taskUrl}";

        return $message;
    }

    public function useWhatsappTemplate()
    {
        return config('services.whatsapp.use_templates', false) &&
               config('services.whatsapp.provider') === 'meta';
    }

    public function toWhatsappTemplate($notifiable)
    {
        return [
            'name' => config('services.whatsapp.templates.task_assigned', 'hello_world'),
            'language' => 'en_US',
            // If your template has variables, add them here:
            // 'components' => [
            //     [
            //         'type' => 'body',
            //         'parameters' => [
            //             ['type' => 'text', 'text' => $this->task->title],
            //             ['type' => 'text', 'text' => $this->task->scheduled_at->format('M d, Y H:i')],
            //             ['type' => 'text', 'text' => ucfirst($this->task->priority)],
            //         ],
            //     ],
            // ],
        ];
    }
}
