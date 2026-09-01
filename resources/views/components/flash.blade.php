@php
    $flashes = collect([
        'success' => session('success'),
        'error' => session('error'),
        'status' => session('status'),
    ])->filter();
@endphp

@if ($flashes->isNotEmpty() || $errors->any())
    <div class="mb-5 space-y-3">

        @foreach ($flashes as $type => $message)
            @php
                $isError = $type === 'error';
            @endphp
            <div
                x-data="{ show: true }"
                x-show="show"
                x-cloak
                x-init="setTimeout(() => show = false, 6000)"
                x-transition.duration.300ms
                role="status"
                @class([
                    'flex items-start gap-3 rounded-xl border p-4 shadow-sm',
                    'border-rose-200 bg-rose-50 text-rose-900 dark:border-rose-500/30 dark:bg-rose-500/10 dark:text-rose-200' => $isError,
                    'border-emerald-200 bg-emerald-50 text-emerald-900 dark:border-emerald-500/30 dark:bg-emerald-500/10 dark:text-emerald-200' => ! $isError,
                ])
            >
                <svg @class([
                    'mt-0.5 size-5 shrink-0',
                    'text-rose-600 dark:text-rose-400' => $isError,
                    'text-emerald-600 dark:text-emerald-400' => ! $isError,
                ]) fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    @if ($isError)
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" />
                    @else
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    @endif
                </svg>

                <p class="flex-1 text-sm font-medium">{{ $message }}</p>

                <button
                    type="button"
                    @click="show = false"
                    class="shrink-0 rounded-md p-0.5 opacity-60 transition hover:opacity-100"
                    aria-label="Dismiss message"
                >
                    <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        @endforeach

        {{-- A summary of validation failures, in addition to the per-field messages. --}}
        @if ($errors->any())
            <div
                x-data="{ show: true }"
                x-show="show"
                role="alert"
                class="flex items-start gap-3 rounded-xl border border-rose-200 bg-rose-50 p-4 text-rose-900 shadow-sm dark:border-rose-500/30 dark:bg-rose-500/10 dark:text-rose-200"
            >
                <svg class="mt-0.5 size-5 shrink-0 text-rose-600 dark:text-rose-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" />
                </svg>

                <div class="flex-1 text-sm">
                    <p class="font-semibold">
                        {{ $errors->count() === 1 ? 'There is a problem with your submission' : 'There are '.$errors->count().' problems with your submission' }}
                    </p>
                    <ul class="mt-1.5 list-inside list-disc space-y-0.5">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>

                <button type="button" @click="show = false"
                    class="shrink-0 rounded-md p-0.5 opacity-60 transition hover:opacity-100" aria-label="Dismiss errors">
                    <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        @endif
    </div>
@endif
