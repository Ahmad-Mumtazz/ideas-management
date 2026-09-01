<?php

namespace App\Policies;

use App\Models\Idea;
use App\Models\User;

class IdeaPolicy
{
    /**
     * Every ability on an idea reduces to the same question: does this idea
     * belong to the authenticated user? Nothing here is shared between users,
     * so there is no role or team dimension to consider.
     */
    protected function owns(User $user, Idea $idea): bool
    {
        return $user->id === $idea->user_id;
    }

    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Idea $idea): bool
    {
        return $this->owns($user, $idea);
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, Idea $idea): bool
    {
        return $this->owns($user, $idea);
    }

    public function delete(User $user, Idea $idea): bool
    {
        return $this->owns($user, $idea);
    }

    public function archive(User $user, Idea $idea): bool
    {
        return $this->owns($user, $idea) && ! $idea->isArchived();
    }

    public function restore(User $user, Idea $idea): bool
    {
        return $this->owns($user, $idea) && $idea->isArchived();
    }
}
