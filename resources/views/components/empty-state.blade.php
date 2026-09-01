@props([
    'title' => 'Nothing here yet',
    'message' => null,
    'icon' => 'M12 18v-5.25m0 0a6.01 6.01 0 001.5-.189m-1.5.189a6.01 6.01 0 01-1.5-.189m3.75 7.478a12.06 12.06 0 01-4.5 0m3.75 2.383a14.406 14.406 0 01-3 0M14.25 18v-.192c0-.983.658-1.823 1.508-2.316a7.5 7.5 0 10-7.517 0c.85.493 1.509 1.333 1.509 2.316V18',
])

<div {{ $attributes->merge(['class' => 'card-surface flex flex-col items-center px-6 py-14 text-center']) }}>
    <span class="grid size-14 place-items-center rounded-2xl bg-indigo-50 text-indigo-600 dark:bg-indigo-500/15 dark:text-indigo-400">
        <svg class="size-7" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="{{ $icon }}" />
        </svg>
    </span>

    <h3 class="mt-4 text-base font-semibold text-slate-900 dark:text-white">{{ $title }}</h3>

    @if ($message)
        <p class="mt-1.5 max-w-sm text-sm text-slate-500 dark:text-slate-400">{{ $message }}</p>
    @endif

    @if (trim($slot) !== '')
        <div class="mt-6">{{ $slot }}</div>
    @endif
</div>
