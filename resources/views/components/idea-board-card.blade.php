@php
    use App\Enums\IdeaPriority;
@endphp

<a
    href="{{ route('ideas.show', $idea) }}"
    draggable="true"
    data-idea-id="{{ $idea->id }}"
    class="idea-board-card group block rounded-lg border border-slate-200 bg-white p-4 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md dark:border-slate-800 dark:bg-slate-900"
    {{-- Title --}}
    <div class="flex items-start justify-between gap-3">
        <h3 class="min-w-0 font-semibold leading-snug text-slate-900 dark:text-white">
            {{ $idea->title }}
        </h3>

        {{-- Priority --}}
        <span
            class="shrink-0 text-xs font-semibold"
            @class([
                'text-rose-600 dark:text-rose-400' => $idea->priority === IdeaPriority::High,
                'text-amber-600 dark:text-amber-400' => $idea->priority === IdeaPriority::Medium,
                'text-slate-500 dark:text-slate-400' => $idea->priority === IdeaPriority::Low,
            ])
        >
            {{ $idea->priority->label() }}
        </span>
    </div>

    {{-- Description --}}
    @if ($idea->description)
        <p class="mt-2 line-clamp-2 text-xs leading-relaxed text-slate-500 dark:text-slate-400">
            {{ $idea->description }}
        </p>
    @endif

    {{-- Progress --}}
    <div class="mt-4">
        <div class="mb-1.5 flex items-center justify-between text-xs">
            <span class="text-slate-500 dark:text-slate-400">
                Progress
            </span>

            <span class="font-semibold tabular-nums text-slate-700 dark:text-slate-300">
                {{ $idea->progress }}%
            </span>
        </div>

        <div class="h-1.5 overflow-hidden rounded-full bg-slate-100 dark:bg-slate-800">
            <div
                class="h-full rounded-full transition-all {{ $idea->progressColor }}"
                style="width: {{ $idea->progress }}%"
            ></div>
        </div>
    </div>

    {{-- Footer --}}
    <div class="mt-4 flex items-center justify-between gap-3 text-xs text-slate-500 dark:text-slate-400">

        {{-- Checkpoints --}}
        <span>
            {{ $idea->completedCheckpoints() }}/{{ $idea->totalCheckpoints() }}
            checkpoints
        </span>

        {{-- Due date --}}
        @if ($idea->due_date)
            <span
                @class([
                    'font-medium text-rose-600 dark:text-rose-400' => $idea->isOverdue(),
                    'font-medium text-amber-600 dark:text-amber-400' => ! $idea->isOverdue() && $idea->isDueSoon(),
                ])
            >
                {{ $idea->isOverdue() ? 'Overdue' : $idea->due_date->format('M j') }}
            </span>
        @endif

    </div>
</a>
