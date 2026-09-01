<?php

namespace App\Policies;

use App\Models\IdeaFile;
use App\Models\User;

class IdeaFilePolicy
{
    /**
     * A stored file is owned by whoever owns its parent idea. This gates the
     * download route, so private documents are never served without a check.
     */
    protected function owns(User $user, IdeaFile $file): bool
    {
        return $user->id === $file->idea->user_id;
    }

    public function view(User $user, IdeaFile $file): bool
    {
        return $this->owns($user, $file);
    }

    public function delete(User $user, IdeaFile $file): bool
    {
        return $this->owns($user, $file);
    }
}
