<x-layout title="Capture and finish your ideas">

    <div class="py-12 sm:py-20">

        <div class="mx-auto max-w-2xl text-center">
            <span class="inline-flex items-center gap-2 rounded-full bg-indigo-50 px-3 py-1 text-xs font-medium text-indigo-700 ring-1 ring-inset ring-indigo-500/20 dark:bg-indigo-500/15 dark:text-indigo-300 dark:ring-indigo-400/25">
                Your private idea workspace
            </span>

            <h1 class="mt-5 text-4xl font-bold tracking-tight text-balance text-slate-900 sm:text-5xl dark:text-white">
                Capture the idea. Break it down. Finish it.
            </h1>

            <p class="mx-auto mt-5 max-w-xl text-base text-pretty text-slate-600 dark:text-slate-400">
                Keep every idea in one place with checkpoints, files, links and due dates —
                and watch the progress bar fill as you tick things off.
            </p>

            <div class="mt-8 flex flex-col justify-center gap-3 sm:flex-row">
                <a href="{{ route('register') }}"
                    class="rounded-lg bg-indigo-600 px-5 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-500">
                    Get started — it's free
                </a>
                <a href="{{ route('login') }}"
                    class="rounded-lg border border-slate-300 px-5 py-3 text-sm font-semibold text-slate-700 transition hover:bg-slate-100 dark:border-slate-700 dark:text-slate-200 dark:hover:bg-slate-800">
                    I already have an account
                </a>
            </div>
        </div>

        <div class="mx-auto mt-16 grid max-w-5xl gap-5 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ([
                [
                    'title' => 'Track real progress',
                    'body' => 'Add checkpoints to any idea and tick them off. The progress bar fills automatically as you go.',
                    'icon' => 'M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
                ],
                [
                    'title' => 'Everything in one place',
                    'body' => 'Attach documents, reference links and a cover image so the whole context lives with the idea.',
                    'icon' => 'M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z',
                ],
                [
                    'title' => 'Private by default',
                    'body' => 'Your ideas, files and images are yours alone — every request is authorized against the owner.',
                    'icon' => 'M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z',
                ],
                [
                    'title' => 'Find it fast',
                    'body' => 'Search, filter by status, priority or category, and sort by due date, progress or recent activity.',
                    'icon' => 'M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z',
                ],
                [
                    'title' => 'Archive, don\'t delete',
                    'body' => 'Park ideas you are not working on right now, and restore them the moment they matter again.',
                    'icon' => 'M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5m16.5 0H3.375',
                ],
                [
                    'title' => 'Works everywhere',
                    'body' => 'A responsive interface with a proper light and dark mode, from your phone to your desktop.',
                    'icon' => 'M10.5 1.5H8.25A2.25 2.25 0 006 3.75v16.5a2.25 2.25 0 002.25 2.25h7.5A2.25 2.25 0 0018 20.25V3.75a2.25 2.25 0 00-2.25-2.25H13.5m-3 0V3h3V1.5m-3 0h3',
                ],
            ] as $feature)
                <div class="card-surface p-6">
                    <span class="grid size-10 place-items-center rounded-lg bg-indigo-50 text-indigo-600 dark:bg-indigo-500/15 dark:text-indigo-400">
                        <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="{{ $feature['icon'] }}" />
                        </svg>
                    </span>
                    <h3 class="mt-4 font-semibold text-slate-900 dark:text-white">{{ $feature['title'] }}</h3>
                    <p class="mt-1.5 text-sm text-slate-600 dark:text-slate-400">{{ $feature['body'] }}</p>
                </div>
            @endforeach
        </div>
    </div>

</x-layout>
