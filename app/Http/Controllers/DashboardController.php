<?php

namespace App\Http\Controllers;

use App\Enums\IdeaPriority;
use App\Enums\IdeaStatus;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * An overview of the authenticated user's own ideas. Every query below is
     * rooted in `$request->user()->ideas()`, so no other user's data is counted.
     */
    public function __invoke(Request $request): View
    {
        $user = $request->user();

        // Lightweight rows (id + status + checkpoint counts) used for the
        // aggregate progress figure without loading full idea records.
        $activeIdeas = $user->ideas()->active()
            ->select('ideas.id', 'ideas.status')
            ->withProgress()
            ->get();

        $statusCounts = $user->ideas()->active()
            ->selectRaw('status, count(*) as aggregate')
            ->groupBy('status')
            ->pluck('aggregate', 'status');

        $priorityCounts = $user->ideas()->active()
            ->selectRaw('priority, count(*) as aggregate')
            ->groupBy('priority')
            ->pluck('aggregate', 'priority');

        return view('dashboard', [
            'total' => $activeIdeas->count(),
            'archivedCount' => $user->ideas()->archived()->count(),

            'statusCounts' => collect(IdeaStatus::cases())
                ->mapWithKeys(fn (IdeaStatus $s) => [$s->value => (int) ($statusCounts[$s->value] ?? 0)]),

            'priorityCounts' => collect(IdeaPriority::cases())
                ->mapWithKeys(fn (IdeaPriority $p) => [$p->value => (int) ($priorityCounts[$p->value] ?? 0)]),

            // Mean completion across active ideas; 0 when there are none.
            'overallProgress' => $activeIdeas->isEmpty()
                ? 0
                : (int) round($activeIdeas->avg(fn ($idea) => $idea->progress)),

            'checkpointsDone' => (int) $activeIdeas->sum(fn ($idea) => $idea->completedCheckpoints()),
            'checkpointsTotal' => (int) $activeIdeas->sum(fn ($idea) => $idea->totalCheckpoints()),

            'overdue' => $user->ideas()->active()->overdue()
                ->withProgress()->orderBy('due_date')->limit(5)->get(),

            'dueSoon' => $user->ideas()->active()->dueWithin(7)
                ->withProgress()->orderBy('due_date')->limit(5)->get(),

            'highPriority' => $user->ideas()->active()
                ->where('priority', IdeaPriority::High->value)
                ->where('status', '!=', IdeaStatus::Completed->value)
                ->withProgress()->latest('updated_at')->limit(5)->get(),

            'recent' => $user->ideas()->active()
                ->withProgress()->latest('updated_at')->limit(6)->get(),
        ]);
    }
}
