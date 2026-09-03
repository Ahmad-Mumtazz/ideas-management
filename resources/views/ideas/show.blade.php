<x-layout :title="$idea->title" wide>

    {{-- Breadcrumb + actions --}}
    <div class="mb-6">
        <a href="{{ route('ideas.index', $idea->isArchived() ? ['view' => 'archived'] : []) }}"
            class="inline-flex items-center gap-1.5 text-sm font-medium text-slate-500 transition hover:text-slate-900 dark:text-slate-400 dark:hover:text-white">
            <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
            </svg>
            {{ $idea->isArchived() ? 'Back to archive' : 'Back to ideas' }}
        </a>

        <div class="mt-3 flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
            <div class="min-w-0">
                <div class="mb-2.5 flex flex-wrap items-center gap-2">
                    <x-status-badge :status="$idea->status" />
                    <x-priority-badge :priority="$idea->priority" />

                    @if ($idea->isArchived())
                    <span class="inline-flex items-center gap-1.5 rounded-full bg-slate-200 px-2.5 py-1 text-xs font-medium text-slate-700 dark:bg-slate-700 dark:text-slate-300">
                        Archived {{ $idea->archived_at->diffForHumans() }}
                    </span>
                    @elseif ($idea->isOverdue())
                    <span class="inline-flex items-center gap-1.5 rounded-full bg-rose-100 px-2.5 py-1 text-xs font-medium text-rose-800 ring-1 ring-inset ring-rose-500/20 dark:bg-rose-500/15 dark:text-rose-300 dark:ring-rose-400/25">
                        Overdue
                    </span>
                    @endif
                </div>

                <h1 class="text-2xl font-bold tracking-tight text-balance text-slate-900 sm:text-3xl dark:text-white">
                    {{ $idea->title }}
                </h1>
            </div>

            {{-- Each destructive action sits in its own form, never nested. --}}
            <div class="flex shrink-0 flex-wrap items-center gap-2">
                <a href="{{ route('ideas.edit', $idea) }}"
                    class="inline-flex items-center gap-1.5 rounded-lg border border-slate-300 px-3.5 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-50 dark:border-slate-700 dark:text-slate-200 dark:hover:bg-slate-800">
                    <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931z" />
                    </svg>
                    Edit
                </a>

                @if ($idea->isArchived())
                <form method="POST" action="{{ route('ideas.restore', $idea) }}">
                    @csrf
                    @method('PATCH')
                    <button type="submit"
                        class="inline-flex items-center gap-1.5 rounded-lg border border-emerald-300 px-3.5 py-2 text-sm font-semibold text-emerald-700 transition hover:bg-emerald-50 dark:border-emerald-500/40 dark:text-emerald-400 dark:hover:bg-emerald-500/10">
                        <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 15L3 9m0 0l6-6M3 9h12a6 6 0 010 12h-3" />
                        </svg>
                        Restore
                    </button>
                </form>
                @else
                <x-confirm-form
                    :action="route('ideas.archive', $idea)"
                    method="PATCH"
                    title="Archive this idea?"
                    message="It will be moved out of your active list. Nothing is deleted and you can restore it at any time."
                    confirm="Archive idea"
                    :danger="false">
                    <button type="button"
                        class="inline-flex items-center gap-1.5 rounded-lg border border-slate-300 px-3.5 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-50 dark:border-slate-700 dark:text-slate-200 dark:hover:bg-slate-800">
                        <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z" />
                        </svg>
                        Archive
                    </button>
                </x-confirm-form>
                @endif

                <x-confirm-form
                    :action="route('ideas.destroy', $idea)"
                    title="Move this idea to Trash?"
                    :message="'“'.$idea->title.'” will be moved to Trash. You can restore it later from the Trash page.'"
                    confirm="Move to Trash">
                    <button type="button"
                        class="inline-flex items-center gap-1.5 rounded-lg border border-rose-300 px-3.5 py-2 text-sm font-semibold text-rose-700 transition hover:bg-rose-50 dark:border-rose-500/40 dark:text-rose-400 dark:hover:bg-rose-500/10">
                        <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
                        </svg>
                        Delete
                    </button>
                </x-confirm-form>
            </div>
        </div>
    </div>

    <div class="grid gap-6 lg:grid-cols-3">

        {{-- ============================ MAIN ============================ --}}
        <div class="space-y-6 lg:col-span-2">

            @if ($idea->cover_image)
            <div class="card-surface overflow-hidden">
                <img src="{{ $idea->coverImageUrl() }}" alt="Cover image for {{ $idea->title }}"
                    class="max-h-96 w-full bg-slate-100 object-cover dark:bg-slate-800">
            </div>
            @endif

            <div class="card-surface p-5 sm:p-6">
                <h2 class="mb-3 text-sm font-semibold text-slate-900 dark:text-white">Description</h2>
                <p class="text-sm leading-relaxed whitespace-pre-line text-slate-700 dark:text-slate-300">{{ $idea->description }}</p>
            </div>

            {{-- ---------------------- Checkpoints ---------------------- --}}
            <section class="card-surface p-5 sm:p-6">
                <div class="mb-4 flex items-center justify-between gap-3">
                    <div>
                        <h2 class="text-sm font-semibold text-slate-900 dark:text-white">Checkpoints</h2>
                        <p class="mt-0.5 text-xs text-slate-500 dark:text-slate-400">
                            Tick these off as you go — progress updates automatically.
                        </p>
                    </div>
                    <span class="shrink-0 rounded-full bg-slate-100 px-2.5 py-1 text-xs font-semibold tabular-nums text-slate-600 dark:bg-slate-800 dark:text-slate-300">
                        {{ $idea->completedCheckpoints() }}/{{ $idea->totalCheckpoints() }}
                    </span>
                </div>

                <x-progress-bar :idea="$idea" class="mb-5" />

                @if ($idea->checkpoints->isEmpty())
                <p class="rounded-xl border border-dashed border-slate-300 py-8 text-center text-sm text-slate-500 dark:border-slate-700 dark:text-slate-400">
                    No checkpoints yet. Add the first step below.
                </p>
                @else
                <ul class="divide-y divide-slate-100 dark:divide-slate-800">
                    @foreach ($idea->checkpoints as $checkpoint)
                    <li x-data="{ editing: false }" class="py-2.5">

                        <div x-show="! editing" class="flex items-center gap-3">
                            {{-- Toggle: its own form so it never nests another --}}
                            <form method="POST" action="{{ route('checkpoints.toggle', $checkpoint) }}" class="flex shrink-0 items-center">
                                @csrf
                                @method('PATCH')
                                <input
                                    type="checkbox"
                                    @checked($checkpoint->is_completed)
                                onchange="this.form.submit()"
                                class="size-5 cursor-pointer rounded border-slate-300 text-emerald-600 focus:ring-emerald-500 dark:border-slate-600 dark:bg-slate-800"
                                aria-label="Mark &quot;{{ $checkpoint->title }}&quot; as {{ $checkpoint->is_completed ? 'incomplete' : 'complete' }}"
                                >
                            </form>

                            <span @class([ 'min-w-0 flex-1 text-sm break-words' , 'text-slate-400 line-through dark:text-slate-500'=> $checkpoint->is_completed,
                                'text-slate-800 dark:text-slate-200' => ! $checkpoint->is_completed,
                                ])>
                                {{ $checkpoint->title }}
                            </span>

                            @if ($checkpoint->is_completed && $checkpoint->completed_at)
                            <span class="hidden shrink-0 text-xs text-slate-400 sm:block dark:text-slate-500">
                                {{ $checkpoint->completed_at->diffForHumans(short: true) }}
                            </span>
                            @endif

                            <div class="flex shrink-0 items-center gap-0.5">
                                <button type="button" @click="editing = true"
                                    class="grid size-8 place-items-center rounded-lg text-slate-400 transition hover:bg-slate-100 hover:text-slate-700 dark:hover:bg-slate-800 dark:hover:text-slate-200"
                                    aria-label="Edit checkpoint">
                                    <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931z" />
                                    </svg>
                                </button>

                                <x-confirm-form
                                    :action="route('checkpoints.destroy', $checkpoint)"
                                    title="Delete this checkpoint?"
                                    :message="'“'.$checkpoint->title.'” will be removed and your progress will be recalculated.'"
                                    confirm="Delete checkpoint">
                                    <button type="button"
                                        class="grid size-8 place-items-center rounded-lg text-slate-400 transition hover:bg-rose-50 hover:text-rose-600 dark:hover:bg-rose-500/10 dark:hover:text-rose-400"
                                        aria-label="Delete checkpoint">
                                        <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
                                        </svg>
                                    </button>
                                </x-confirm-form>
                            </div>
                        </div>

                        {{-- Inline rename --}}
                        <form x-show="editing" x-cloak method="POST"
                            action="{{ route('checkpoints.update', $checkpoint) }}" class="flex items-center gap-2">
                            @csrf
                            @method('PATCH')
                            <input type="text" name="title" value="{{ $checkpoint->title }}" required maxlength="200"
                                class="field flex-1" x-ref="input" @keydown.escape="editing = false">
                            <button type="submit"
                                class="rounded-lg bg-indigo-600 px-3 py-2 text-sm font-semibold text-white transition hover:bg-indigo-500">
                                Save
                            </button>
                            <button type="button" @click="editing = false"
                                class="rounded-lg border border-slate-300 px-3 py-2 text-sm font-semibold text-slate-600 transition hover:bg-slate-50 dark:border-slate-700 dark:text-slate-300 dark:hover:bg-slate-800">
                                Cancel
                            </button>
                        </form>
                    </li>
                    @endforeach
                </ul>
                @endif

                <form method="POST" action="{{ route('checkpoints.store', $idea) }}" class="mt-4 flex flex-col gap-2 border-t border-slate-100 pt-4 sm:flex-row dark:border-slate-800">
                    @csrf
                    <input type="text" name="title" required maxlength="200" placeholder="Add a checkpoint…"
                        class="field flex-1 @error('title') field-error @enderror">
                    <button type="submit"
                        class="inline-flex items-center justify-center gap-1.5 rounded-lg bg-slate-900 px-4 py-2 text-sm font-semibold text-white transition hover:bg-slate-700 dark:bg-white dark:text-slate-900 dark:hover:bg-slate-200">
                        <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="2.2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                        </svg>
                        Add
                    </button>
                </form>
            </section>

            {{-- ------------------------- Links ------------------------- --}}
            <section class="card-surface p-5 sm:p-6">
                <div class="mb-4">
                    <h2 class="text-sm font-semibold text-slate-900 dark:text-white">Links</h2>
                    <p class="mt-0.5 text-xs text-slate-500 dark:text-slate-400">
                        References, research and anything else worth keeping to hand.
                    </p>
                </div>

                @if ($idea->links->isEmpty())
                <p class="rounded-xl border border-dashed border-slate-300 py-8 text-center text-sm text-slate-500 dark:border-slate-700 dark:text-slate-400">
                    No links added yet.
                </p>
                @else
                <ul class="space-y-2">
                    @foreach ($idea->links as $link)
                    <li x-data="{ editing: false }"
                        class="rounded-xl border border-slate-200 p-3 transition hover:border-slate-300 dark:border-slate-800 dark:hover:border-slate-700">

                        <div x-show="! editing" class="flex items-center gap-3">
                            <span class="grid size-9 shrink-0 place-items-center rounded-lg bg-cyan-50 text-cyan-600 dark:bg-cyan-500/15 dark:text-cyan-400">
                                <svg class="size-4.5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M13.19 8.688a4.5 4.5 0 011.242 7.244l-4.5 4.5a4.5 4.5 0 01-6.364-6.364l1.757-1.757m13.35-.622l1.757-1.757a4.5 4.5 0 00-6.364-6.364l-4.5 4.5a4.5 4.5 0 001.242 7.244" />
                                </svg>
                            </span>

                            <div class="min-w-0 flex-1">
                                <a href="{{ $link->url }}" target="_blank" rel="noopener noreferrer nofollow"
                                    class="block truncate text-sm font-medium text-slate-900 hover:text-indigo-600 dark:text-white dark:hover:text-indigo-400">
                                    {{ $link->label }}
                                </a>
                                <p class="truncate text-xs text-slate-500 dark:text-slate-400">{{ $link->host ?: $link->url }}</p>
                            </div>

                            <div class="flex shrink-0 items-center gap-0.5">
                                <button type="button" @click="editing = true"
                                    class="grid size-8 place-items-center rounded-lg text-slate-400 transition hover:bg-slate-100 hover:text-slate-700 dark:hover:bg-slate-800 dark:hover:text-slate-200"
                                    aria-label="Edit link">
                                    <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931z" />
                                    </svg>
                                </button>

                                <x-confirm-form
                                    :action="route('links.destroy', $link)"
                                    title="Remove this link?"
                                    :message="'“'.$link->label.'” will be removed from this idea.'"
                                    confirm="Remove link">
                                    <button type="button"
                                        class="grid size-8 place-items-center rounded-lg text-slate-400 transition hover:bg-rose-50 hover:text-rose-600 dark:hover:bg-rose-500/10 dark:hover:text-rose-400"
                                        aria-label="Remove link">
                                        <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                        </svg>
                                    </button>
                                </x-confirm-form>
                            </div>
                        </div>

                        <form x-show="editing" x-cloak method="POST" action="{{ route('links.update', $link) }}"
                            class="grid gap-2 sm:grid-cols-[1fr_2fr_auto]">
                            @csrf
                            @method('PATCH')
                            <input type="text" name="label" value="{{ $link->label }}" required maxlength="100"
                                class="field" placeholder="Label" @keydown.escape="editing = false">
                            <input type="text" name="url" value="{{ $link->url }}" required
                                class="field" placeholder="https://…" @keydown.escape="editing = false">
                            <div class="flex gap-2">
                                <button type="submit"
                                    class="flex-1 rounded-lg bg-indigo-600 px-3 py-2 text-sm font-semibold text-white transition hover:bg-indigo-500">
                                    Save
                                </button>
                                <button type="button" @click="editing = false"
                                    class="rounded-lg border border-slate-300 px-3 py-2 text-sm font-semibold text-slate-600 transition hover:bg-slate-50 dark:border-slate-700 dark:text-slate-300 dark:hover:bg-slate-800">
                                    Cancel
                                </button>
                            </div>
                        </form>
                    </li>
                    @endforeach
                </ul>
                @endif

                <form method="POST" action="{{ route('links.store', $idea) }}"
                    class="mt-4 grid gap-2 border-t border-slate-100 pt-4 sm:grid-cols-[1fr_2fr_auto] dark:border-slate-800">
                    @csrf
                    <input type="text" name="label" value="{{ old('label') }}" required maxlength="100"
                        placeholder="Label" class="field @error('label') field-error @enderror">
                    <input type="text" name="url" value="{{ old('url') }}" required
                        placeholder="https://example.com" class="field @error('url') field-error @enderror">
                    <button type="submit"
                        class="rounded-lg bg-slate-900 px-4 py-2 text-sm font-semibold text-white transition hover:bg-slate-700 dark:bg-white dark:text-slate-900 dark:hover:bg-slate-200">
                        Add link
                    </button>
                </form>
            </section>

            {{-- ------------------------- Files ------------------------- --}}
            <section class="card-surface p-5 sm:p-6">
                <div class="mb-4">
                    <h2 class="text-sm font-semibold text-slate-900 dark:text-white">Files</h2>
                    <p class="mt-0.5 text-xs text-slate-500 dark:text-slate-400">
                        Stored privately — only you can download these.
                    </p>
                </div>

                @if ($idea->files->isEmpty())
                <p class="rounded-xl border border-dashed border-slate-300 py-8 text-center text-sm text-slate-500 dark:border-slate-700 dark:text-slate-400">
                    No files uploaded yet.
                </p>
                @else
                <ul class="space-y-2">
                    @foreach ($idea->files as $file)
                    <li class="flex items-center gap-3 rounded-xl border border-slate-200 p-3 transition hover:border-slate-300 dark:border-slate-800 dark:hover:border-slate-700">
                        <span class="grid size-10 shrink-0 place-items-center rounded-lg bg-amber-50 text-[10px] font-bold text-amber-700 dark:bg-amber-500/15 dark:text-amber-400">
                            {{ Str::limit($file->extension, 4, '') }}
                        </span>

                        <div class="min-w-0 flex-1">
                            <p class="truncate text-sm font-medium text-slate-900 dark:text-white">{{ $file->original_name }}</p>
                            <p class="text-xs text-slate-500 dark:text-slate-400">
                                {{ $file->readable_size }} · {{ $file->created_at->format('j M Y') }}
                            </p>
                        </div>

                        <div class="flex shrink-0 items-center gap-0.5">
                            <a href="{{ route('files.show', $file) }}"
                                class="grid size-8 place-items-center rounded-lg text-slate-400 transition hover:bg-slate-100 hover:text-indigo-600 dark:hover:bg-slate-800 dark:hover:text-indigo-400"
                                aria-label="Download {{ $file->original_name }}">
                                <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3" />
                                </svg>
                            </a>

                            <x-confirm-form
                                :action="route('files.destroy', $file)"
                                title="Delete this file?"
                                :message="'“'.$file->original_name.'” will be permanently removed from storage. This cannot be undone.'"
                                confirm="Delete file">
                                <button type="button"
                                    class="grid size-8 place-items-center rounded-lg text-slate-400 transition hover:bg-rose-50 hover:text-rose-600 dark:hover:bg-rose-500/10 dark:hover:text-rose-400"
                                    aria-label="Delete {{ $file->original_name }}">
                                    <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
                                    </svg>
                                </button>
                            </x-confirm-form>
                        </div>
                    </li>
                    @endforeach
                </ul>
                @endif

                <form method="POST" action="{{ route('files.store', $idea) }}" enctype="multipart/form-data"
                    x-data="{ count: 0 }"
                    class="mt-4 border-t border-slate-100 pt-4 dark:border-slate-800">
                    @csrf
                    <div class="flex flex-col gap-2 sm:flex-row sm:items-center">
                        <input type="file" name="files[]" multiple required
                            @change="count = $event.target.files.length"
                            class="block w-full flex-1 text-sm text-slate-600 file:mr-3 file:rounded-lg file:border-0 file:bg-indigo-50 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-indigo-700 hover:file:bg-indigo-100 dark:text-slate-400 dark:file:bg-indigo-500/15 dark:file:text-indigo-300">
                        <button type="submit"
                            class="shrink-0 rounded-lg bg-slate-900 px-4 py-2 text-sm font-semibold text-white transition hover:bg-slate-700 dark:bg-white dark:text-slate-900 dark:hover:bg-slate-200">
                            Upload <span x-show="count > 0" x-text="`(${count})`"></span>
                        </button>
                    </div>
                    <p class="mt-2 text-xs text-slate-500 dark:text-slate-400">
                        {{-- Real limits, capped by PHP's upload_max_filesize / post_max_size. --}}
                        Documents, spreadsheets, archives and images. Up to
                        {{ \App\Http\Requests\IdeaFileRequest::maxFiles() }} files,
                        {{ \App\Support\UploadLimits::label(\App\Http\Requests\IdeaFileRequest::maxKilobytes()) }} each.
                    </p>
                    <x-input-error :messages="$errors->get('files')" />
                    <x-input-error :messages="$errors->get('files.*')" />
                </form>
            </section>
        </div>

        {{-- =========================== SIDEBAR =========================== --}}
        <div class="space-y-6">

            {{-- Progress --}}
            <div class="card-surface p-5 sm:p-6">
                <h2 class="text-sm font-semibold text-slate-900 dark:text-white">Progress</h2>
                <p class="mt-3 text-4xl font-bold tracking-tight tabular-nums text-slate-900 dark:text-white">
                    {{ $idea->progress }}<span class="text-2xl text-slate-400">%</span>
                </p>
                <x-progress-bar :idea="$idea" class="mt-3" />
            </div>

            {{-- Details --}}
            <div class="card-surface p-5 sm:p-6">
                <h2 class="mb-4 text-sm font-semibold text-slate-900 dark:text-white">Details</h2>

                <dl class="space-y-3.5 text-sm">
                    <div class="flex items-center justify-between gap-3">
                        <dt class="text-slate-500 dark:text-slate-400">Status</dt>
                        <dd><x-status-badge :status="$idea->status" /></dd>
                    </div>

                    <div class="flex items-center justify-between gap-3">
                        <dt class="text-slate-500 dark:text-slate-400">Priority</dt>
                        <dd><x-priority-badge :priority="$idea->priority" /></dd>
                    </div>

                    <div class="flex items-center justify-between gap-3">
                        <dt class="text-slate-500 dark:text-slate-400">Category</dt>
                        <dd class="min-w-0 truncate text-right font-medium text-slate-900 dark:text-white">
                            {{ $idea->category ?: '—' }}
                        </dd>
                    </div>

                    <div class="flex items-center justify-between gap-3">
                        <dt class="text-slate-500 dark:text-slate-400">Due date</dt>
                        <dd @class([ 'text-right font-medium' , 'text-rose-600 dark:text-rose-400'=> $idea->isOverdue(),
                            'text-slate-900 dark:text-white' => ! $idea->isOverdue(),
                            ])>
                            {{ $idea->due_date?->format('j M Y') ?: '—' }}
                        </dd>
                    </div>

                    <div class="flex items-center justify-between gap-3">
                        <dt class="text-slate-500 dark:text-slate-400">Created</dt>
                        <dd class="text-right font-medium text-slate-900 dark:text-white">
                            {{ $idea->created_at->format('j M Y') }}
                        </dd>
                    </div>

                    <div class="flex items-center justify-between gap-3">
                        <dt class="text-slate-500 dark:text-slate-400">Last updated</dt>
                        <dd class="text-right font-medium text-slate-900 dark:text-white">
                            {{ $idea->updated_at->diffForHumans() }}
                        </dd>
                    </div>
                </dl>

                @if (filled($idea->tags))
                <div class="mt-4 border-t border-slate-100 pt-4 dark:border-slate-800">
                    <p class="mb-2 text-xs text-slate-500 dark:text-slate-400">Tags</p>
                    <div class="flex flex-wrap gap-1.5">
                        @foreach ($idea->tags as $tag)
                        <a href="{{ route('ideas.index', ['search' => $tag]) }}"
                            class="inline-flex items-center rounded-md bg-slate-100 px-2 py-1 text-xs text-slate-600 transition hover:bg-indigo-50 hover:text-indigo-700 dark:bg-slate-800 dark:text-slate-400 dark:hover:bg-indigo-500/15 dark:hover:text-indigo-300">
                            #{{ $tag }}
                        </a>
                        @endforeach
                    </div>
                </div>
                @endif
            </div>

            {{-- Activity --}}
            <div class="card-surface p-5 sm:p-6">
                <h2 class="mb-4 text-sm font-semibold text-slate-900 dark:text-white">Activity</h2>

                @if ($idea->activities->isEmpty())
                <p class="text-sm text-slate-500 dark:text-slate-400">No activity recorded yet.</p>
                @else
                <ol class="relative space-y-4 border-l border-slate-200 pl-5 dark:border-slate-800">
                    @foreach ($idea->activities as $activity)
                    <li class="relative">
                        <span class="absolute top-0.5 -left-[1.6875rem] grid size-6 place-items-center rounded-full bg-white ring-4 ring-white dark:bg-slate-900 dark:ring-slate-900">
                            <svg class="size-4 {{ $activity->icon_color }}" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="{{ $activity->icon }}" />
                            </svg>
                        </span>

                        <p class="text-sm text-slate-800 dark:text-slate-200">{{ $activity->description }}</p>
                        <p class="mt-0.5 text-xs text-slate-400 dark:text-slate-500">
                            <time datetime="{{ $activity->created_at->toIso8601String() }}"
                                title="{{ $activity->created_at->format('j M Y, H:i') }}">
                                {{ $activity->created_at->diffForHumans() }}
                            </time>
                        </p>
                    </li>
                    @endforeach
                </ol>
                @endif
            </div>
        </div>
    </div>

</x-layout>