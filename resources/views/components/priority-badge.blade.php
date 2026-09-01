@props(['priority'])

<span {{ $attributes->merge([
    'class' => 'inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-xs font-medium ring-1 ring-inset '.$priority->badgeClasses(),
]) }}>
    <svg class="size-3" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
        <path d="M3 3a1 1 0 011-1h12a1 1 0 01.8 1.6L14.25 7l2.55 3.4A1 1 0 0116 12H5v5a1 1 0 11-2 0V3z" />
    </svg>
    {{ $priority->label() }}
</span>
