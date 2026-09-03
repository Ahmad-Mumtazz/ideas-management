<?php

namespace App\Notifications;

use App\Models\Idea;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class IdeaDueSoon extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        public Idea $idea
    ) {
        //
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
{
    return [
        'type' => 'due_soon',
        'title' => 'Idea due soon',
        'message' => '"' . $this->idea->title . '" is due on ' . $this->idea->due_date->format('M j, Y') . '.',
        'idea_id' => $this->idea->id,
        'due_date' => $this->idea->due_date->toDateString(),
    ];
}
}