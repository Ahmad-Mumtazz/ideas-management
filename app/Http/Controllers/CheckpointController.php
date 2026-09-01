<?php

namespace App\Http\Controllers;

use App\Http\Requests\CheckpointRequest;
use App\Models\Checkpoint;
use App\Models\Idea;
use Illuminate\Http\RedirectResponse;

class CheckpointController extends Controller
{
    /**
     * Add a checkpoint to an idea the current user owns.
     */
    public function store(CheckpointRequest $request, Idea $idea): RedirectResponse
    {
        $this->authorize('update', $idea);

        $idea->checkpoints()->create([
            'title' => $request->validated('title'),
            'position' => (int) $idea->checkpoints()->max('position') + 1,
        ]);

        return back()->with('success', 'Checkpoint added.');
    }

    public function update(CheckpointRequest $request, Checkpoint $checkpoint): RedirectResponse
    {
        $this->authorize('update', $checkpoint);

        $checkpoint->update(['title' => $request->validated('title')]);

        return back()->with('success', 'Checkpoint updated.');
    }

    /**
     * Flip a single checkpoint between complete and incomplete. The new state is
     * persisted, which is what the idea's progress percentage is derived from.
     */
    public function toggle(Checkpoint $checkpoint): RedirectResponse
    {
        $this->authorize('update', $checkpoint);

        $checkpoint->toggle();

        $checkpoint->idea->recordActivity(
            'checkpoint',
            ($checkpoint->is_completed ? 'Completed' : 'Reopened').' checkpoint: '.$checkpoint->title
        );

        return back()->with(
            'success',
            $checkpoint->is_completed
                ? 'Checkpoint marked as completed.'
                : 'Checkpoint marked as incomplete.'
        );
    }

    public function destroy(Checkpoint $checkpoint): RedirectResponse
    {
        $this->authorize('delete', $checkpoint);

        $checkpoint->delete();

        return back()->with('success', 'Checkpoint deleted.');
    }
}
