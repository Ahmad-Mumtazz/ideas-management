@props([
    'action',
    'method' => 'DELETE',
    'title' => 'Are you sure?',
    'message' => 'This action cannot be undone.',
    'confirm' => 'Delete',
    'danger' => true,
])

@php
    $modalId = 'confirm-'.Str::random(8);
@endphp

{{--
    A confirmation gate for destructive actions. The trigger is whatever is
    passed in the default slot; the real form only submits once confirmed.
    The dialog is teleported to <body> so it is never clipped by a card, and it
    is sized to sit comfortably on small screens as well as desktop.
--}}
<div x-data="{ open: false }" {{ $attributes->only('class') }}>

    <div @click="open = true">
        {{ $slot }}
    </div>

    <template x-teleport="body">
        <div
            x-show="open"
            x-cloak
            @keydown.escape.window="open = false"
            class="fixed inset-0 z-50 flex items-end justify-center p-4 sm:items-center"
            role="dialog"
            aria-modal="true"
            aria-labelledby="{{ $modalId }}-title"
        >
            {{-- Backdrop --}}
            <div
                x-show="open"
                x-transition.opacity
                @click="open = false"
                class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm"
            ></div>

            {{-- Panel --}}
            <div
                x-show="open"
                x-transition
                class="relative w-full max-w-md rounded-2xl border border-slate-200 bg-white p-6 shadow-xl dark:border-slate-800 dark:bg-slate-900"
            >
                <div class="flex items-start gap-4">
                    <span @class([
                        'grid size-11 shrink-0 place-items-center rounded-full',
                        'bg-rose-100 text-rose-600 dark:bg-rose-500/15 dark:text-rose-400' => $danger,
                        'bg-amber-100 text-amber-600 dark:bg-amber-500/15 dark:text-amber-400' => ! $danger,
                    ])>
                        <svg class="size-6" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                            </svg>
                    </span>

                    <div class="min-w-0 flex-1">
                        <h3 id="{{ $modalId }}-title" class="text-base font-semibold text-slate-900 dark:text-white">
                            {{ $title }}
                        </h3>
                        <p class="mt-1.5 text-sm text-slate-600 dark:text-slate-400">
                            {{ $message }}
                        </p>
                    </div>
                </div>

                <div class="mt-6 flex flex-col-reverse gap-2 sm:flex-row sm:justify-end">
                    <button
                        type="button"
                        @click="open = false"
                        class="w-full rounded-lg border border-slate-300 px-4 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-50 sm:w-auto dark:border-slate-700 dark:text-slate-200 dark:hover:bg-slate-800"
                    >
                        Cancel
                    </button>

                    <form method="POST" action="{{ $action }}" class="w-full sm:w-auto">
                        @csrf
                        @method($method)
                        <button
                            type="submit"
                            @class([
                                'w-full rounded-lg px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition sm:w-auto',
                                'bg-rose-600 hover:bg-rose-500' => $danger,
                                'bg-amber-600 hover:bg-amber-500' => ! $danger,
                            ])
                        >
                            {{ $confirm }}
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </template>
</div>
