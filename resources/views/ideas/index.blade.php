@php
    use App\Enums\IdeaPriority;
    use App\Enums\IdeaStatus;
    use App\Models\Idea;

    $isArchived = $filters['view'] === 'archived';
    $hasFilters = $filters['search'] || $filters['status'] || $filters['priority'] || $filters['category'];
@endphp

<x-layout :title="$isArchived ? 'Archived ideas' : 'Ideas'" wide>

    {{-- Header --}}
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900 sm:text-3xl dark:text-white">
                {{ $isArchived ? 'Archived ideas' : 'Your ideas' }}
            </h1>
            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                {{ $ideas->total() }} {{ Str::plural('idea', $ideas->total()) }}
                @if ($hasFilters) matching your filters @endif
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

    {{-- Active / archived tabs --}}
    <div class="mb-5 inline-flex rounded-lg border border-slate-200 bg-white p-1 dark:border-slate-800 dark:bg-slate-900">
        <a href="{{ route('ideas.index') }}" @class([
            'rounded-md px-3.5 py-1.5 text-sm font-medium transition',
            'bg-indigo-600 text-white shadow-sm' => ! $isArchived,
            'text-slate-600 hover:text-slate-900 dark:text-slate-400 dark:hover:text-white' => $isArchived,
        ])>
            Active <span class="ml-1 tabular-nums opacity-75">{{ $activeCount }}</span>
        </a>
        <a href="{{ route('ideas.index', ['view' => 'archived']) }}" @class([
            'rounded-md px-3.5 py-1.5 text-sm font-medium transition',
            'bg-indigo-600 text-white shadow-sm' => $isArchived,
            'text-slate-600 hover:text-slate-900 dark:text-slate-400 dark:hover:text-white' => ! $isArchived,
        ])>
            Archived <span class="ml-1 tabular-nums opacity-75">{{ $archivedCount }}</span>
        </a>
    </div>

    {{--
        Filters. A plain GET form, so the current view is fully described by the
        URL and is shareable, bookmarkable and back-button friendly.
    --}}
    <form
        method="GET"
        action="{{ route('ideas.index') }}"
        x-data="{ advanced: {{ ($filters['status'] || $filters['priority'] || $filters['category']) ? 'true' : 'false' }} }"
        class="card-surface mb-6 p-4"
    >
        @if ($isArchived)
            <input type="hidden" name="view" value="archived">
        @endif

        <div class="flex flex-col gap-3 sm:flex-row">
            <div class="relative flex-1">
                <svg class="pointer-events-none absolute top-1/2 left-3 size-4 -translate-y-1/2 text-slate-400"
                    fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
                </svg>
                <input
                    type="search"
                    name="search"
                    value="{{ $filters['search'] }}"
                    placeholder="Search titles, descriptions, categories and tags…"
                    class="field pl-9"
                    aria-label="Search ideas"
                >
            </div>

            <select name="sort" class="field sm:w-52" aria-label="Sort ideas" onchange="this.form.submit()">
                @foreach (Idea::sortOptions() as $value => $label)
                    <option value="{{ $value }}" @selected($filters['sort'] === $value)>{{ $label }}</option>
                @endforeach
            </select>

            <div class="flex gap-2">
                <button type="submit"
                    class="flex-1 rounded-lg bg-slate-900 px-4 py-2 text-sm font-semibold text-white transition hover:bg-slate-700 sm:flex-none dark:bg-white dark:text-slate-900 dark:hover:bg-slate-200">
                    Search
                </button>

                <button type="button" @click="advanced = ! advanced"
                    class="rounded-lg border border-slate-300 px-3 py-2 text-sm font-medium text-slate-600 transition hover:bg-slate-50 dark:border-slate-700 dark:text-slate-300 dark:hover:bg-slate-800"
                    :aria-expanded="advanced">
                    <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M12 3c2.755 0 5.455.232 8.083.678.533.09.917.556.917 1.096v1.044a2.25 2.25 0 01-.659 1.591l-5.432 5.432a2.25 2.25 0 00-.659 1.591v2.927a2.25 2.25 0 01-1.244 2.013L9.75 21v-6.568a2.25 2.25 0 00-.659-1.591L3.659 7.409A2.25 2.25 0 013 5.818V4.774c0-.54.384-1.006.917-1.096A48.32 48.32 0 0112 3z" />
                    </svg>
                    <span class="sr-only">Toggle filters</span>
                </button>
            </div>
        </div>

        <div x-show="advanced" x-cloak x-collapse>
            <div class="mt-4 grid gap-3 border-t border-slate-100 pt-4 sm:grid-cols-3 dark:border-slate-800">
                <div>
                    <label for="filter-status" class="label">Status</label>
                    <select id="filter-status" name="status" class="field">
                        <option value="">All statuses</option>
                        @foreach (IdeaStatus::options() as $value => $label)
                            <option value="{{ $value }}" @selected($filters['status'] === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="filter-priority" class="label">Priority</label>
                    <select id="filter-priority" name="priority" class="field">
                        <option value="">All priorities</option>
                        @foreach (IdeaPriority::options() as $value => $label)
                            <option value="{{ $value }}" @selected($filters['priority'] === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="filter-category" class="label">Category</label>
                    <select id="filter-category" name="category" class="field" @disabled($categories->isEmpty())>
                        <option value="">{{ $categories->isEmpty() ? 'No categories yet' : 'All categories' }}</option>
                        @foreach ($categories as $category)
                            <option value="{{ $category }}" @selected($filters['category'] === $category)>{{ $category }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            @if ($hasFilters)
                <div class="mt-3 flex justify-end">
                    <a href="{{ route('ideas.index', $isArchived ? ['view' => 'archived'] : []) }}"
                        class="text-sm font-medium text-slate-500 underline-offset-2 hover:text-slate-900 hover:underline dark:text-slate-400 dark:hover:text-white">
                        Clear all filters
                    </a>
                </div>
            @endif
        </div>
    </form>

    {{-- Results --}}
    @if ($ideas->isEmpty())

        @if ($hasFilters)
            <x-empty-state
                title="No ideas match those filters"
                message="Try a different search term, or clear the filters to see everything again."
                icon="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z"
            >
                <a href="{{ route('ideas.index', $isArchived ? ['view' => 'archived'] : []) }}"
                    class="inline-flex items-center gap-2 rounded-lg border border-slate-300 px-4 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-50 dark:border-slate-700 dark:text-slate-200 dark:hover:bg-slate-800">
                    Clear filters
                </a>
            </x-empty-state>
        @elseif ($isArchived)
            <x-empty-state
                title="Nothing archived"
                message="Ideas you archive will be kept here so you can restore them later."
                icon="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5m16.5 0H3.375"
            >
                <a href="{{ route('ideas.index') }}"
                    class="inline-flex items-center gap-2 rounded-lg border border-slate-300 px-4 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-50 dark:border-slate-700 dark:text-slate-200 dark:hover:bg-slate-800">
                    Back to active ideas
                </a>
            </x-empty-state>
        @else
            <x-empty-state
                title="No ideas yet"
                message="Capture your first idea, then break it into checkpoints you can tick off as you go."
            >
                <a href="{{ route('ideas.create') }}"
                    class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-500">
                    Create your first idea
                </a>
            </x-empty-state>
        @endif

    @else

        <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($ideas as $idea)
                <x-idea-card :idea="$idea" />
            @endforeach
        </div>

        @if ($ideas->hasPages())
            <div class="mt-8">
                {{ $ideas->links('pagination') }}
            </div>
        @endif

    @endif

</x-layout>
