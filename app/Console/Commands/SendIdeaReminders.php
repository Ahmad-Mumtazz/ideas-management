<?php

namespace App\Console\Commands;

use App\Models\Idea;
use App\Notifications\IdeaDueSoon;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('app:send-idea-reminders')]
#[Description('Send notifications for ideas that are due soon')]
class SendIdeaReminders extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $ideas = Idea::query()
            ->active()
            ->dueWithin(7)
            ->with('user')
            ->get();

        $sent = 0;

        foreach ($ideas as $idea) {
            $alreadyNotified = $idea->user
                ->notifications()
                ->where('type', IdeaDueSoon::class)
                ->where('data->idea_id', $idea->id)
                ->where('data->due_date', $idea->due_date->toDateString())
                ->exists();

            if ($alreadyNotified) {
                continue;
            }

            $idea->user->notify(new IdeaDueSoon($idea));

            $sent++;

            $this->info('Reminder sent: '.$idea->title);
        }

        $this->info("Total reminders sent: {$sent}");

        return self::SUCCESS;
    }
}