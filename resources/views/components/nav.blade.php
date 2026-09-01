@php
    $links = auth()->check()
        ? [
            ['route' => 'dashboard', 'label' => 'Dashboard', 'active' => request()->routeIs('dashboard')],
            ['route' => 'ideas.index', 'label' => 'Ideas', 'active' => request()->routeIs('ideas.index', 'ideas.show')],
            ['route' => 'ideas.create', 'label' => 'New Idea', 'active' => request()->routeIs('ideas.create')],
        ]
        : [];
@endphp

<nav
    x-data="{ open: false }"
    @keydown.escape.window="open = false"
    class="sticky top-0 z-40 border-b border-slate-200 bg-white/90 backdrop-blur dark:border-slate-800 dark:bg-slate-950/90"
>
    <div class="mx-auto flex h-16 max-w-7xl items-center gap-3 px-4 sm:px-6 lg:px-8">

        {{-- Brand --}}
        <a
            href="{{ auth()->check() ? route('dashboard') : route('home') }}"
            class="flex shrink-0 items-center gap-2 font-semibold tracking-tight text-slate-900 dark:text-white"
        >
            <span class="grid size-8 place-items-center rounded-lg bg-gradient-to-br from-indigo-500 to-violet-600 text-white shadow-sm">
                <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M12 18v-5.25m0 0a6.01 6.01 0 001.5-.189m-1.5.189a6.01 6.01 0 01-1.5-.189m3.75 7.478a12.06 12.06 0 01-4.5 0m3.75 2.383a14.406 14.406 0 01-3 0M14.25 18v-.192c0-.983.658-1.823 1.508-2.316a7.5 7.5 0 10-7.517 0c.85.493 1.509 1.333 1.509 2.316V18" />
                </svg>
            </span>
            <span class="text-lg">Ideas</span>
        </a>

        {{-- Desktop links --}}
        <div class="ml-4 hidden items-center gap-1 lg:flex">
            @foreach ($links as $link)
                <a
                    href="{{ route($link['route']) }}"
                    @class([
                        'rounded-lg px-3 py-2 text-sm font-medium transition',
                        'bg-indigo-50 text-indigo-700 dark:bg-indigo-500/15 dark:text-indigo-300' => $link['active'],
                        'text-slate-600 hover:bg-slate-100 hover:text-slate-900 dark:text-slate-300 dark:hover:bg-slate-800 dark:hover:text-white' => ! $link['active'],
                    ])
                >
                    {{ $link['label'] }}
                </a>
            @endforeach
        </div>

        <div class="ml-auto flex items-center gap-2">

            {{-- Theme toggle --}}
            <button
                type="button"
                @click="$store.theme.toggle()"
                class="grid size-9 place-items-center rounded-lg text-slate-500 transition hover:bg-slate-100 hover:text-slate-900 dark:text-slate-400 dark:hover:bg-slate-800 dark:hover:text-white"
                :aria-label="$store.theme.dark ? 'Switch to light mode' : 'Switch to dark mode'"
                aria-label="Toggle colour theme"
            >
                {{-- Sun (shown in dark mode) --}}
                <svg x-show="$store.theme.dark" x-cloak class="size-5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M12 3v2.25m6.364.386l-1.591 1.591M21 12h-2.25m-.386 6.364l-1.591-1.591M12 18.75V21m-4.773-4.227l-1.591 1.591M5.25 12H3m4.227-4.773L5.636 5.636M15.75 12a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0z" />
                </svg>
                {{-- Moon (shown in light mode) --}}
                <svg x-show="! $store.theme.dark" class="size-5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M21.752 15.002A9.72 9.72 0 0118 15.75c-5.385 0-9.75-4.365-9.75-9.75 0-1.33.266-2.597.748-3.752A9.753 9.753 0 003 11.25C3 16.635 7.365 21 12.75 21a9.753 9.753 0 009.002-5.998z" />
                </svg>
            </button>

            @guest
                <a href="{{ route('login') }}"
                    class="hidden rounded-lg px-3 py-2 text-sm font-medium text-slate-600 transition hover:bg-slate-100 hover:text-slate-900 sm:block dark:text-slate-300 dark:hover:bg-slate-800 dark:hover:text-white">
                    Log in
                </a>
                <a href="{{ route('register') }}"
                    class="rounded-lg bg-indigo-600 px-3.5 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-500">
                    Get started
                </a>
            @endguest

            @auth
                {{-- Desktop account menu --}}
                <div class="relative hidden lg:block" x-data="{ menu: false }" @click.outside="menu = false">
                    <button
                        type="button"
                        @click="menu = ! menu"
                        class="flex items-center gap-2 rounded-lg p-1 pr-2 transition hover:bg-slate-100 dark:hover:bg-slate-800"
                        :aria-expanded="menu"
                        aria-haspopup="true"
                    >
                        <x-avatar :user="auth()->user()" class="size-8" />
                        <svg class="size-4 text-slate-400 transition" :class="menu && 'rotate-180'" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                        </svg>
                    </button>

                    <div
                        x-show="menu"
                        x-cloak
                        x-transition.origin.top.right
                        class="absolute right-0 mt-2 w-56 overflow-hidden rounded-xl border border-slate-200 bg-white py-1 shadow-lg dark:border-slate-800 dark:bg-slate-900"
                    >
                        <div class="border-b border-slate-100 px-4 py-3 dark:border-slate-800">
                            <p class="truncate text-sm font-semibold text-slate-900 dark:text-white">{{ auth()->user()->name }}</p>
                            <p class="truncate text-xs text-slate-500 dark:text-slate-400">{{ auth()->user()->email }}</p>
                        </div>

                        <a href="{{ route('profile.edit') }}"
                            class="block px-4 py-2 text-sm text-slate-700 transition hover:bg-slate-50 dark:text-slate-300 dark:hover:bg-slate-800">
                            Edit profile
                        </a>
                        <a href="{{ route('ideas.index', ['view' => 'archived']) }}"
                            class="block px-4 py-2 text-sm text-slate-700 transition hover:bg-slate-50 dark:text-slate-300 dark:hover:bg-slate-800">
                            Archived ideas
                        </a>

                        <form method="POST" action="{{ route('logout') }}" class="border-t border-slate-100 dark:border-slate-800">
                            @csrf
                            @method('DELETE')
                            <button type="submit"
                                class="block w-full px-4 py-2 text-left text-sm text-rose-600 transition hover:bg-rose-50 dark:text-rose-400 dark:hover:bg-rose-500/10">
                                Log out
                            </button>
                        </form>
                    </div>
                </div>
            @endauth

            {{-- Mobile menu button --}}
            <button
                type="button"
                @click="open = ! open"
                class="grid size-9 place-items-center rounded-lg text-slate-600 transition hover:bg-slate-100 lg:hidden dark:text-slate-300 dark:hover:bg-slate-800"
                :aria-expanded="open"
                aria-label="Toggle navigation menu"
            >
                <svg x-show="! open" class="size-6" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
                </svg>
                <svg x-show="open" x-cloak class="size-6" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
    </div>

    {{-- Mobile drawer --}}
    <div x-show="open" x-cloak x-collapse class="border-t border-slate-200 lg:hidden dark:border-slate-800">
        <div class="space-y-1 px-4 py-3 sm:px-6">

            @auth
                <div class="mb-3 flex items-center gap-3 rounded-xl bg-slate-50 p-3 dark:bg-slate-900">
                    <x-avatar :user="auth()->user()" class="size-10" />
                    <div class="min-w-0">
                        <p class="truncate text-sm font-semibold text-slate-900 dark:text-white">{{ auth()->user()->name }}</p>
                        <p class="truncate text-xs text-slate-500 dark:text-slate-400">{{ auth()->user()->email }}</p>
                    </div>
                </div>

                @foreach ($links as $link)
                    <a href="{{ route($link['route']) }}" @class([
                        'block rounded-lg px-3 py-2.5 text-sm font-medium transition',
                        'bg-indigo-50 text-indigo-700 dark:bg-indigo-500/15 dark:text-indigo-300' => $link['active'],
                        'text-slate-700 hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-slate-800' => ! $link['active'],
                    ])>
                        {{ $link['label'] }}
                    </a>
                @endforeach

                <a href="{{ route('ideas.index', ['view' => 'archived']) }}"
                    class="block rounded-lg px-3 py-2.5 text-sm font-medium text-slate-700 transition hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-slate-800">
                    Archived
                </a>
                <a href="{{ route('profile.edit') }}"
                    class="block rounded-lg px-3 py-2.5 text-sm font-medium text-slate-700 transition hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-slate-800">
                    Edit profile
                </a>

                <form method="POST" action="{{ route('logout') }}" class="pt-1">
                    @csrf
                    @method('DELETE')
                    <button type="submit"
                        class="block w-full rounded-lg px-3 py-2.5 text-left text-sm font-medium text-rose-600 transition hover:bg-rose-50 dark:text-rose-400 dark:hover:bg-rose-500/10">
                        Log out
                    </button>
                </form>
            @endauth

            @guest
                <a href="{{ route('login') }}"
                    class="block rounded-lg px-3 py-2.5 text-sm font-medium text-slate-700 transition hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-slate-800">
                    Log in
                </a>
                <a href="{{ route('register') }}"
                    class="block rounded-lg px-3 py-2.5 text-sm font-medium text-slate-700 transition hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-slate-800">
                    Create an account
                </a>
            @endguest
        </div>
    </div>
</nav>
