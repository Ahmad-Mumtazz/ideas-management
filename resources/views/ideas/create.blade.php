<x-layout title="New idea">

    <div class="mb-6">
        <a href="{{ route('ideas.index') }}"
            class="inline-flex items-center gap-1.5 text-sm font-medium text-slate-500 transition hover:text-slate-900 dark:text-slate-400 dark:hover:text-white">
            <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
            </svg>
            Back to ideas
        </a>

        <h1 class="mt-3 text-2xl font-bold tracking-tight text-slate-900 sm:text-3xl dark:text-white">
            New idea
        </h1>
        <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
            Capture it now — you can add checkpoints, links and files once it's saved.
        </p>
    </div>

    <x-idea-form
        :idea="$idea"
        :action="route('ideas.store')"
        :categories="$categories"
        submit-label="Create idea"
    />

</x-layout>
