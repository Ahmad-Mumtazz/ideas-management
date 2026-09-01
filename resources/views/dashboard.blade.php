@php
    use App\Enums\IdeaPriority;
    use App\Enums\IdeaStatus;
@endphp

<x-layout title="Dashboard" wide>

    {{-- Header --}}
    <div class="mb-7 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900 sm:text-3xl dark:text-white">
                Hello, {{ Str::before(auth()->user()->name, ' ') }}
            </h1>
            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                Here's where your ideas stand today.
            </p>
        </div>

        <a href="{{ route('ideas.create') }}"
            class="inline-flex shrink-0 items-center justify-center gap-2 rounded-lg bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-500">
            <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="2.2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
            </svg>
            New idea
        </a>
    </div>

    @if ($total === 0 && $archivedCount === 0)

        <x-empty-state
            title="No ideas yet"
            message="Create your first idea and start breaking it into checkpoints you can tick off."
        >
            <a href="{{ route('ideas.create') }}"
                class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-500">
                Create your first idea
            </a>
        </x-empty-state>

    @else

        {{-- Headline numbers --}}
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <x-stat-card
                label="Active ideas"
                :value="$total"
                tone="indigo"
                :href="route('ideas.index')"
                icon="M12 18v-5.25m0 0a6.01 6.01 0 001.5-.189m-1.5.189a6.01 6.01 0 01-1.5-.189m3.75 7.478a12.06 12.06 0 01-4.5 0m3.75 2.383a14.406 14.406 0 01-3 0M14.25 18v-.192c0-.983.658-1.823 1.508-2.316a7.5 7.5 0 10-7.517 0c.85.493 1.509 1.333 1.509 2.316V18"
                :hint="$archivedCount > 0 ? $archivedCount.' archived' : null"
            />

            <x-stat-card
                label="Pending"
                :value="$statusCounts[IdeaStatus::Pending->value]"
                tone="slate"
                :href="route('ideas.index', ['status' => IdeaStatus::Pending->value])"
                icon="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"
            />

            <x-stat-card
                label="In progress"
                :value="$statusCounts[IdeaStatus::InProgress->value]"
                tone="sky"
                :href="route('ideas.index', ['status' => IdeaStatus::InProgress->value])"
                icon="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99"
            />

            <x-stat-card
                label="Completed"
                :value="$statusCounts[IdeaStatus::Completed->value]"
                tone="emerald"
                :href="route('ideas.index', ['status' => IdeaStatus::Completed->value])"
                icon="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
            />
        </div>

        <div class="mt-5 grid gap-5 lg:grid-cols-3">

            {{-- Overall progress --}}
            <div class="card-surface p-6 lg:col-span-1">
                <h2 class="text-sm font-semibold text-slate-900 dark:text-white">Overall progress</h2>
                <p class="mt-0.5 text-xs text-slate-500 dark:text-slate-400">
                    Average completion across your active ideas
                </p>

                @php
                    $circumference = 2 * M_PI * 52;
                    $dash = $circumference * ($overallProgress / 100);
                @endphp

                <div class="mt-5 flex items-center gap-6">
                    <div class="relative size-32 shrink-0">
                        <svg class="size-32 -rotate-90" viewBox="0 0 120 120" aria-hidden="true">
                            <circle cx="60" cy="60" r="52" fill="none" stroke-width="12"
                                class="stroke-slate-200 dark:stroke-slate-800" />
                            <circle cx="60" cy="60" r="52" fill="none" stroke-width="12" stroke-linecap="round"
                                class="stroke-indigo-500 transition-all duration-700"
                                stroke-dasharray="{{ $dash }} {{ $circumference }}" />
                        </svg>
                        <div class="absolute inset-0 grid place-items-center">
                            <span class="text-2xl font-bold tabular-nums text-slate-900 dark:text-white">
                                {{ $overallProgress }}%
                            </span>
                        </div>
                    </div>

                    <div class="min-w-0 space-y-3 text-sm">
                        <div>
                            <p class="font-semibold tabular-nums text-slate-900 dark:text-white">
                                {{ $checkpointsDone }} / {{ $checkpointsTotal }}
                            </p>
                            <p class="text-xs text-slate-500 dark:text-slate-400">Checkpoints completed</p>
                        </div>
                        <div>
                            <p class="font-semibold tabular-nums text-slate-900 dark:text-white">
                                {{ $priorityCounts[IdeaPriority::High->value] }}
                            </p>
                            <p class="text-xs text-slate-500 dark:text-slate-400">High priority</p>
                        </div>
                    </div>
                </div>

                {{-- Status split --}}
                <div class="mt-6 space-y-2.5">
                    @foreach (IdeaStatus::cases() as $case)
                        @php
                            $count = $statusCounts[$case->value];
                            $pct = $total > 0 ? round(($count / $total) * 100) : 0;
                        @endphp
                        <div>
                            <div class="mb-1 flex items-center justify-between text-xs">
                                <span class="text-slate-600 dark:text-slate-400">{{ $case->label() }}</span>
                                <span class="font-medium tabular-nums text-slate-700 dark:text-slate-300">{{ $count }}</span>
                            </div>
                            <div class="h-1.5 w-full overflow-hidden rounded-full bg-slate-200 dark:bg-slate-800">
                                <div class="h-1.5 rounded-full {{ $case->dotClasses() }} transition-all duration-500"
                                    style="width: {{ $pct }}%"></div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- Needs attention --}}
            <div class="card-surface p-6 lg:col-span-2">
                <h2 class="text-sm font-semibold text-slate-900 dark:text-white">Needs attention</h2>
                <p class="mt-0.5 text-xs text-slate-500 dark:text-slate-400">
                    Overdue, due this week, and high priority
                </p>

                @php
                    $attention = collect()
                        ->concat($overdue->map(fn ($i) => ['idea' => $i, 'flag' => 'overdue']))
                        ->concat($dueSoon->map(fn ($i) => ['idea' => $i, 'flag' => 'due']))
                        ->concat($highPriority->map(fn ($i) => ['idea' => $i, 'flag' => 'high']))
                        ->unique(fn ($row) => $row['idea']->id)
                        ->take(6);
                @endphp

                @if ($attention->isEmpty())
                    <div class="mt-6 flex flex-col items-center gap-2 rounded-xl border border-dashed border-slate-300 py-10 text-center dark:border-slate-700">
                        <svg class="size-8 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <p class="text-sm font-medium text-slate-700 dark:text-slate-300">Nothing is overdue</p>
                        <p class="text-xs text-slate-500 dark:text-slate-400">You're on top of everything right now.</p>
                    </div>
                @else
                    <ul class="mt-4 divide-y divide-slate-100 dark:divide-slate-800">
                        @foreach ($attention as $row)
                            @php $idea = $row['idea']; @endphp
                            <li>
                                <a href="{{ route('ideas.show', $idea) }}"
                                    class="-mx-2 flex items-center gap-3 rounded-lg px-2 py-3 transition hover:bg-slate-50 dark:hover:bg-slate-800/60">

                                    <span @class([
                                        'grid size-9 shrink-0 place-items-center rounded-lg',
                                        'bg-rose-100 text-rose-600 dark:bg-rose-500/15 dark:text-rose-400' => $row['flag'] === 'overdue',
                                        'bg-amber-100 text-amber-600 dark:bg-amber-500/15 dark:text-amber-400' => $row['flag'] === 'due',
                                        'bg-indigo-100 text-indigo-600 dark:bg-indigo-500/15 dark:text-indigo-400' => $row['flag'] === 'high',
                                    ])>
                                        <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5" />
                                        </svg>
                                    </span>

                                    <div class="min-w-0 flex-1">
                                        <p class="truncate text-sm font-medium text-slate-900 dark:text-white">
                                            {{ $idea->title }}
                                        </p>
                                        <p class="mt-0.5 text-xs text-slate-500 dark:text-slate-400">
                                            @if ($idea->due_date)
                                                @if ($idea->isOverdue())
                                                    <span class="font-medium text-rose-600 dark:text-rose-400">
                                                        Overdue — was due {{ $idea->due_date->format('j M') }}
                                                    </span>
                                                @else
                                                    Due {{ $idea->due_date->diffForHumans() }}
                                                @endif
                                            @else
                                                High priority · {{ $idea->status->label() }}
                                            @endif
                                        </p>
                                    </div>

                                    <div class="hidden w-28 shrink-0 sm:block">
                                        <x-progress-bar :idea="$idea" :show-label="false" size="sm" />
                                    </div>

                                    <span class="shrink-0 text-xs font-semibold tabular-nums text-slate-500 dark:text-slate-400">
                                        {{ $idea->progress }}%
                                    </span>
                                </a>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>
        </div>

        {{-- Recently updated --}}
        @if ($recent->isNotEmpty())
            <div class="mt-8">
                <div class="mb-4 flex items-center justify-between">
                    <h2 class="text-lg font-semibold text-slate-900 dark:text-white">Recently updated</h2>
                    <a href="{{ route('ideas.index', ['sort' => 'updated']) }}"
                        class="text-sm font-medium text-indigo-600 hover:text-indigo-500 dark:text-indigo-400">
                        View all
                    </a>
                </div>

                <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($recent as $idea)
                        <x-idea-card :idea="$idea" />
                    @endforeach
                </div>
            </div>
        @endif

    @endif

</x-layout>
