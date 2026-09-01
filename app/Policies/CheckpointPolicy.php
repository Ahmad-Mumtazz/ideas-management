<?php

namespace App\Policies;

use App\Models\Checkpoint;
use App\Models\User;

class CheckpointPolicy
{
    /**
     * A checkpoint is owned by whoever owns its parent idea.
     */
    protected function owns(User $user, Checkpoint $checkpoint): bool
    {
        return $user->id === $checkpoint->idea->user_id;
    }

    public function view(User $user, Checkpoint $checkpoint): bool
    {
        return $this->owns($user, $checkpoint);
    }

    public function update(User $user, Checkpoint $checkpoint): bool
    {
        return $this->owns($user, $checkpoint);
    }

    public function delete(User $user, Checkpoint $checkpoint): bool
    {
        return $this->owns($user, $checkpoint);
    }
}
