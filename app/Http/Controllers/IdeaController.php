<?php

namespace App\Http\Controllers;

use App\Enums\IdeaPriority;
use App\Enums\IdeaStatus;
use App\Http\Requests\IdeaRequest;
use App\Models\Idea;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class IdeaController extends Controller
{
    /**
     * The owner's ideas, filtered/searched/sorted and paginated.
     */
    public function index(Request $request): View
    {
        $filters = $this->filters($request);

        // Always rooted in the relationship, so another user's rows are not
        // reachable even if a filter value is tampered with.
        $ideas = $request->user()->ideas()
            ->withProgress()
            ->withCount(['files', 'links'])
            ->when($filters['view'] === 'archived', fn ($q) => $q->archived(), fn ($q) => $q->active())
            ->when($filters['status'], fn ($q, $status) => $q->where('status', $status))
            ->when($filters['priority'], fn ($q, $priority) => $q->where('priority', $priority))
            ->when($filters['category'], fn ($q, $category) => $q->where('category', $category))
            ->search($filters['search'])
            ->sorted($filters['sort'])
            ->paginate(9)
            ->withQueryString();

        return view('ideas.index', [
            'ideas' => $ideas,
            'filters' => $filters,
            'categories' => $this->categoriesFor($request),
            'activeCount' => $request->user()->ideas()->active()->count(),
            'archivedCount' => $request->user()->ideas()->archived()->count(),
        ]);
    }

    public function create(): View
    {
        return view('ideas.create', [
            'idea' => new Idea(['status' => IdeaStatus::Pending, 'priority' => IdeaPriority::Medium]),
            'categories' => $this->categoriesFor(request()),
        ]);
    }

    public function store(IdeaRequest $request): RedirectResponse
    {
        $attributes = $request->ideaAttributes();

        if ($request->hasFile('cover_image')) {
            $attributes['cover_image'] = $this->storeCover($request, $request->user()->id);
        }

        // Created through the relationship, so user_id can never come from input.
        $idea = $request->user()->ideas()->create($attributes);

        $idea->recordActivity('created', 'Idea created');

        return redirect()
            ->route('ideas.show', $idea)
            ->with('success', 'Idea created successfully.');
    }

    public function show(Idea $idea): View
    {
        $this->authorize('view', $idea);

        $idea->load([
            'checkpoints',
            'links',
            'files',
            'activities' => fn ($q) => $q->limit(30),
            'activities.user:id,name',
        ])->loadCount([
            'checkpoints',
            'checkpoints as completed_checkpoints_count' => fn ($q) => $q->where('is_completed', true),
        ]);

        return view('ideas.show', compact('idea'));
    }

    public function edit(Idea $idea): View
    {
        $this->authorize('update', $idea);

        return view('ideas.edit', [
            'idea' => $idea,
            'categories' => $this->categoriesFor(request()),
        ]);
    }

    public function update(IdeaRequest $request, Idea $idea): RedirectResponse
    {
        $this->authorize('update', $idea);

        $previousStatus = $idea->status;
        $attributes = $request->ideaAttributes();

        if ($request->hasFile('cover_image')) {
            // Replacing the cover: drop the previous file so it is not orphaned.
            $idea->deleteCoverImage();
            $attributes['cover_image'] = $this->storeCover($request, $idea->user_id);
        } elseif ($request->boolean('remove_cover_image')) {
            $idea->deleteCoverImage();
            $attributes['cover_image'] = null;
        }

        $idea->update($attributes);

        // Self-healing: if this idea has checkpoints, its status is owned by the
        // derivation rather than by anything submitted here. Records its own
        // activity entry when it actually changes something.
        $syncedStatus = $idea->syncStatusFromCheckpoints();

        if (! $syncedStatus && $previousStatus !== $idea->status) {
            $idea->recordActivity(
                'status',
                'Status changed from '.$previousStatus->label().' to '.$idea->status->label()
            );
        } elseif (! $syncedStatus) {
            $idea->recordActivity('updated', 'Idea details updated');
        }

        return redirect()
            ->route('ideas.show', $idea)
            ->with('success', 'Idea updated successfully.');
    }

    /**
     * Permanent removal. Child rows go via the FK cascade and the stored files
     * are removed by the Idea model's `deleting` hook.
     */
    public function destroy(Idea $idea): RedirectResponse
    {
        $this->authorize('delete', $idea);

        $title = $idea->title;
        $idea->delete();

        return redirect()
            ->route('ideas.index')
            ->with('success', '"'.$title.'" was permanently deleted.');
    }

    public function archive(Idea $idea): RedirectResponse
    {
        $this->authorize('archive', $idea);

        $idea->forceFill(['archived_at' => now()])->save();
        $idea->recordActivity('archived', 'Idea archived');

        return back()->with('success', '"'.$idea->title.'" was archived.');
    }

    public function restore(Idea $idea): RedirectResponse
    {
        $this->authorize('restore', $idea);

        $idea->forceFill(['archived_at' => null])->save();
        $idea->recordActivity('restored', 'Idea restored from the archive');

        return back()->with('success', '"'.$idea->title.'" was restored.');
    }

    /**
     * Cover images live on the private disk and are streamed only to their
     * owner, so they cannot be enumerated through a public storage URL.
     */
    public function cover(Idea $idea): StreamedResponse
    {
        $this->authorize('view', $idea);

        abort_if(! $idea->cover_image || ! Storage::disk('local')->exists($idea->cover_image), 404);

        return Storage::disk('local')->response($idea->cover_image, headers: [
            'Cache-Control' => 'private, max-age=3600',
        ]);
    }

    /**
     * Normalised, whitelisted filter values — anything unrecognised is dropped
     * rather than passed through to the query.
     *
     * @return array<string, string|null>
     */
    protected function filters(Request $request): array
    {
        $sort = $request->string('sort')->toString();
        $view = $request->string('view')->toString();
        $status = $request->string('status')->toString();
        $priority = $request->string('priority')->toString();

        return [
            'search' => trim($request->string('search')->toString()) ?: null,
            'status' => in_array($status, IdeaStatus::values(), true) ? $status : null,
            'priority' => in_array($priority, IdeaPriority::values(), true) ? $priority : null,
            'category' => trim($request->string('category')->toString()) ?: null,
            'sort' => array_key_exists($sort, Idea::sortOptions()) ? $sort : 'newest',
            'view' => $view === 'archived' ? 'archived' : 'active',
        ];
    }

    /**
     * Distinct categories across the current user's ideas, for the filter menu.
     *
     * @return Collection<int, string>
     */
    protected function categoriesFor(Request $request)
    {
        return $request->user()->ideas()
            ->whereNotNull('category')
            ->where('category', '!=', '')
            ->distinct()
            ->orderBy('category')
            ->pluck('category');
    }

    protected function storeCover(IdeaRequest $request, int $userId): string
    {
        return $request->file('cover_image')->store('idea-covers/'.$userId, 'local');
    }
}
