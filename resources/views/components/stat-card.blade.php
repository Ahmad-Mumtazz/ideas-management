@props([
    'label',
    'value',
    'icon' => null,
    'tone' => 'slate',
    'href' => null,
    'hint' => null,
])

@php
    $tones = [
        'slate' => 'bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-300',
        'indigo' => 'bg-indigo-100 text-indigo-600 dark:bg-indigo-500/15 dark:text-indigo-400',
        'sky' => 'bg-sky-100 text-sky-600 dark:bg-sky-500/15 dark:text-sky-400',
        'emerald' => 'bg-emerald-100 text-emerald-600 dark:bg-emerald-500/15 dark:text-emerald-400',
        'amber' => 'bg-amber-100 text-amber-600 dark:bg-amber-500/15 dark:text-amber-400',
        'rose' => 'bg-rose-100 text-rose-600 dark:bg-rose-500/15 dark:text-rose-400',
    ];

    $tag = $href ? 'a' : 'div';
@endphp

<{{ $tag }}
    @if ($href) href="{{ $href }}" @endif
    {{ $attributes->merge([
        'class' => 'card-surface block p-5 transition '.($href ? 'hover:border-indigo-300 hover:shadow-md dark:hover:border-indigo-500/40' : ''),
    ]) }}
>
    <div class="flex items-center justify-between gap-3">
        <p class="text-sm font-medium text-slate-500 dark:text-slate-400">{{ $label }}</p>

        @if ($icon)
            <span class="grid size-9 shrink-0 place-items-center rounded-lg {{ $tones[$tone] ?? $tones['slate'] }}">
                <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="{{ $icon }}" />
                </svg>
            </span>
        @endif
    </div>

    <p class="mt-2 text-3xl font-semibold tracking-tight tabular-nums text-slate-900 dark:text-white">
        {{ $value }}
    </p>

    @if ($hint)
        <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">{{ $hint }}</p>
    @endif
</{{ $tag }}>
