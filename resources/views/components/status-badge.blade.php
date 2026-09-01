@props(['status'])

<span {{ $attributes->merge([
    'class' => 'inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-xs font-medium ring-1 ring-inset '.$status->badgeClasses(),
]) }}>
    <span class="size-1.5 rounded-full {{ $status->dotClasses() }}"></span>
    {{ $status->label() }}
</span>
