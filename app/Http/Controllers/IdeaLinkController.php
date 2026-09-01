<?php

namespace App\Http\Controllers;

use App\Http\Requests\IdeaLinkRequest;
use App\Models\Idea;
use App\Models\IdeaLink;
use Illuminate\Http\RedirectResponse;

class IdeaLinkController extends Controller
{
    /**
     * Attach a reference link to an idea the current user owns.
     */
    public function store(IdeaLinkRequest $request, Idea $idea): RedirectResponse
    {
        $this->authorize('update', $idea);

        $link = $idea->links()->create($request->validated());

        $idea->recordActivity('link', 'Added link: '.$link->label);

        return back()->with('success', 'Link added.');
    }

    public function update(IdeaLinkRequest $request, IdeaLink $link): RedirectResponse
    {
        $this->authorize('update', $link);

        $link->update($request->validated());

        return back()->with('success', 'Link updated.');
    }

    public function destroy(IdeaLink $link): RedirectResponse
    {
        $this->authorize('delete', $link);

        $label = $link->label;
        $link->delete();

        return back()->with('success', '"'.$label.'" was removed.');
    }
}
