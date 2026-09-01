@props(['title' => null, 'wide' => false])

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ? $title.' · Ideas' : 'Ideas' }}</title>

    {{--
        Applied before first paint so the page never flashes the wrong theme.
        Falls back to the OS preference until the user makes an explicit choice.
    --}}
    <script>
        (function () {
            try {
                var stored = localStorage.getItem('theme');
                var dark = stored ? stored === 'dark'
                    : window.matchMedia('(prefers-color-scheme: dark)').matches;
                document.documentElement.classList.toggle('dark', dark);
            } catch (e) {
                /* Storage unavailable — fall through to the light theme. */
            }
        })();
    </script>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="flex min-h-full flex-col">

    <x-nav />

    <main @class([
        'mx-auto w-full flex-1 px-4 py-6 sm:px-6 lg:px-8',
        'max-w-7xl' => $wide,
        'max-w-5xl' => ! $wide,
    ])>
        <x-flash />

        {{ $slot }}
    </main>

    <footer class="border-t border-slate-200 py-6 dark:border-slate-800">
        <p class="text-center text-xs text-slate-500 dark:text-slate-400">
            Ideas — capture it, break it down, finish it.
        </p>
    </footer>

</body>

</html>
