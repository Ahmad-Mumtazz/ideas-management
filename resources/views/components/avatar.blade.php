@props(['user'])

{{--
    Renders the user's own profile photo when set, falling back to their
    initials. The photo is served through the authorized profile.photo route,
    not a public storage URL.
--}}
@if ($user->profile_photo && $user->is(auth()->user()))
    <img
        src="{{ $user->profilePhotoUrl() }}"
        alt="{{ $user->name }}"
        {{ $attributes->merge(['class' => 'shrink-0 rounded-full object-cover ring-1 ring-slate-200 dark:ring-slate-700']) }}
    >
@else
    <span
        {{ $attributes->merge([
            'class' => 'grid shrink-0 place-items-center rounded-full bg-gradient-to-br from-indigo-500 to-violet-600 text-xs font-semibold text-white',
        ]) }}
        aria-hidden="true"
    >{{ $user->initials }}</span>
@endif
