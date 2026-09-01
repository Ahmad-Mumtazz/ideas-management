<?php

namespace App\Observers;

use App\Models\Checkpoint;

/**
 * Keeps an idea's status in step with its checkpoints.
 *
 * Hooking the model rather than the controller means the status is recalculated
 * however a checkpoint changes — the toggle route, a rename that happens to
 * touch completion, a seeder, a factory or tinker — so status and progress can
 * never drift apart. The rules themselves live in Idea::deriveStatus().
 */
class CheckpointObserver
{
    public function created(Checkpoint $checkpoint): void
    {
        $this->sync($checkpoint);
    }

    public function updated(Checkpoint $checkpoint): void
    {
        // A rename cannot change the outcome, so only completion changes matter.
        if ($checkpoint->wasChanged('is_completed')) {
            $this->sync($checkpoint);
        }
    }

    public function deleted(Checkpoint $checkpoint): void
    {
        $this->sync($checkpoint);
    }

    /**
     * Deleting an idea cascades its checkpoints at the database level, which
     * fires no model events — but a checkpoint deleted on its own still has a
     * parent, so the relationship is re-queried defensively here.
     */
    protected function sync(Checkpoint $checkpoint): void
    {
        $idea = $checkpoint->idea()->first();

        $idea?->syncStatusFromCheckpoints();
    }
}
