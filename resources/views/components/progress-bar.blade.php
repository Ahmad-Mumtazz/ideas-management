@props(['idea', 'showLabel' => true, 'size' => 'md'])

@php
    $height = $size === 'sm' ? 'h-1.5' : 'h-2.5';
@endphp

<div {{ $attributes->merge(['class' => 'w-full']) }}>
    @if ($showLabel)
        <div class="mb-1.5 flex items-baseline justify-between gap-2">
            <span class="text-xs font-medium text-slate-500 dark:text-slate-400">
                @if ($idea->totalCheckpoints() > 0)
                    {{ $idea->completedCheckpoints() }} of {{ $idea->totalCheckpoints() }} checkpoints
                @else
                    No checkpoints yet
                @endif
            </span>
            <span class="text-xs font-semibold tabular-nums text-slate-700 dark:text-slate-200">
                {{ $idea->progress }}%
            </span>
        </div>
    @endif

    <div
        class="{{ $height }} w-full overflow-hidden rounded-full bg-slate-200 dark:bg-slate-800"
        role="progressbar"
        aria-valuenow="{{ $idea->progress }}"
        aria-valuemin="0"
        aria-valuemax="100"
        aria-label="Idea progress"
    >
        <div
            class="{{ $height }} rounded-full transition-all duration-500 {{ $idea->progress_color }}"
            style="width: {{ max($idea->progress, 2) }}%"
        ></div>
    </div>
</div>
