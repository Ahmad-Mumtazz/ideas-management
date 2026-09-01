{{--
    A single idea in the grid. The cover image leads the card, followed by the
    status/priority badges, title, excerpt, tags, progress and a footer of
    counts. `$idea` is supplied by App\View\Components\IdeaCard.
--}}
<article {{ $attributes->merge([
    'class' => 'card-surface group flex flex-col overflow-hidden transition hover:-translate-y-0.5 hover:border-indigo-300 hover:shadow-lg dark:hover:border-indigo-500/40',
]) }}>

    {{-- Cover image, streamed through the owner-checked route --}}
    <a href="{{ route('ideas.show', $idea) }}" class="block shrink-0">
        @if ($idea->cover_image)
            <img
                src="{{ $idea->coverImageUrl() }}"
                alt=""
                loading="lazy"
                class="aspect-[16/9] w-full bg-slate-100 object-cover dark:bg-slate-800"
            >
        @else
            <div class="grid aspect-[16/9] w-full place-items-center bg-gradient-to-br from-slate-100 to-slate-200 dark:from-slate-800 dark:to-slate-900">
                <svg class="size-9 text-slate-300 dark:text-slate-700" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M12 18v-5.25m0 0a6.01 6.01 0 001.5-.189m-1.5.189a6.01 6.01 0 01-1.5-.189m3.75 7.478a12.06 12.06 0 01-4.5 0m3.75 2.383a14.406 14.406 0 01-3 0M14.25 18v-.192c0-.983.658-1.823 1.508-2.316a7.5 7.5 0 10-7.517 0c.85.493 1.509 1.333 1.509 2.316V18" />
                </svg>
            </div>
        @endif
    </a>

    <div class="flex flex-1 flex-col gap-3 p-5">

        <div class="flex flex-wrap items-center gap-2">
            <x-status-badge :status="$idea->status" />
            <x-priority-badge :priority="$idea->priority" />

            @if ($idea->isOverdue())
                <span class="inline-flex items-center gap-1 rounded-full bg-rose-100 px-2.5 py-1 text-xs font-medium text-rose-800 ring-1 ring-inset ring-rose-500/20 dark:bg-rose-500/15 dark:text-rose-300 dark:ring-rose-400/25">
                    Overdue
                </span>
            @elseif ($idea->isDueSoon())
                <span class="inline-flex items-center gap-1 rounded-full bg-amber-100 px-2.5 py-1 text-xs font-medium text-amber-800 ring-1 ring-inset ring-amber-500/20 dark:bg-amber-500/15 dark:text-amber-300 dark:ring-amber-400/25">
                    Due soon
                </span>
            @endif

            @if ($idea->isArchived())
                <span class="inline-flex items-center gap-1 rounded-full bg-slate-200 px-2.5 py-1 text-xs font-medium text-slate-700 dark:bg-slate-700 dark:text-slate-300">
                    Archived
                </span>
            @endif
        </div>

        <div>
            <h3 class="text-base leading-snug font-semibold text-slate-900 dark:text-white">
                <a href="{{ route('ideas.show', $idea) }}" class="transition group-hover:text-indigo-600 dark:group-hover:text-indigo-400">
                    {{ $idea->title }}
                </a>
            </h3>

            <p class="mt-1.5 line-clamp-2 text-sm text-slate-600 dark:text-slate-400">
                {{ $idea->description }}
            </p>
        </div>

        @if ($idea->category || filled($idea->tags))
            <div class="flex flex-wrap items-center gap-1.5">
                @if ($idea->category)
                    <span class="inline-flex items-center rounded-md bg-indigo-50 px-2 py-1 text-xs font-medium text-indigo-700 dark:bg-indigo-500/15 dark:text-indigo-300">
                        {{ $idea->category }}
                    </span>
                @endif

                @foreach (array_slice($idea->tags ?? [], 0, 3) as $tag)
                    <span class="inline-flex items-center rounded-md bg-slate-100 px-2 py-1 text-xs text-slate-600 dark:bg-slate-800 dark:text-slate-400">
                        #{{ $tag }}
                    </span>
                @endforeach

                @if (count($idea->tags ?? []) > 3)
                    <span class="text-xs text-slate-400 dark:text-slate-500">
                        +{{ count($idea->tags) - 3 }}
                    </span>
                @endif
            </div>
        @endif

        <div class="mt-auto pt-1">
            <x-progress-bar :idea="$idea" />
        </div>

        <div class="flex flex-wrap items-center gap-x-4 gap-y-1 border-t border-slate-100 pt-3 text-xs text-slate-500 dark:border-slate-800 dark:text-slate-400">

            @if ($idea->due_date)
                <span class="inline-flex items-center gap-1 {{ $idea->isOverdue() ? 'font-medium text-rose-600 dark:text-rose-400' : '' }}">
                    <svg class="size-3.5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5" />
                    </svg>
                    {{ $idea->due_date->format('j M Y') }}
                </span>
            @endif

            @if (($idea->files_count ?? 0) > 0)
                <span class="inline-flex items-center gap-1">
                    <svg class="size-3.5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M18.375 12.739l-7.693 7.693a4.5 4.5 0 01-6.364-6.364l10.94-10.94A3 3 0 1119.5 7.372L8.552 18.32m.009-.01l-.01.01m5.699-9.941l-7.81 7.81a1.5 1.5 0 002.112 2.13" />
                    </svg>
                    {{ $idea->files_count }}
                </span>
            @endif

            @if (($idea->links_count ?? 0) > 0)
                <span class="inline-flex items-center gap-1">
                    <svg class="size-3.5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M13.19 8.688a4.5 4.5 0 011.242 7.244l-4.5 4.5a4.5 4.5 0 01-6.364-6.364l1.757-1.757m13.35-.622l1.757-1.757a4.5 4.5 0 00-6.364-6.364l-4.5 4.5a4.5 4.5 0 001.242 7.244" />
                    </svg>
                    {{ $idea->links_count }}
                </span>
            @endif

            <span class="ml-auto">Updated {{ $idea->updated_at->diffForHumans(short: true) }}</span>
        </div>
    </div>
</article>
