<?php

namespace App\Policies;

use App\Models\IdeaLink;
use App\Models\User;

class IdeaLinkPolicy
{
    /**
     * A link is owned by whoever owns its parent idea.
     */
    protected function owns(User $user, IdeaLink $link): bool
    {
        return $user->id === $link->idea->user_id;
    }

    public function view(User $user, IdeaLink $link): bool
    {
        return $this->owns($user, $link);
    }

    public function update(User $user, IdeaLink $link): bool
    {
        return $this->owns($user, $link);
    }

    public function delete(User $user, IdeaLink $link): bool
    {
        return $this->owns($user, $link);
    }
}
