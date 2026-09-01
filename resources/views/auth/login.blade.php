<x-layout title="Log in">

    <div class="flex min-h-[calc(100vh-14rem)] items-center justify-center px-2 py-8">
        <div class="w-full max-w-md">

            <div class="mb-7 text-center">
                <h1 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-white">
                    Welcome back
                </h1>
                <p class="mt-1.5 text-sm text-slate-500 dark:text-slate-400">
                    Log in to pick up where you left off.
                </p>
            </div>

            <form method="POST" action="{{ route('login') }}" class="card-surface space-y-5 p-6 sm:p-7">
                @csrf

                <div>
                    <label for="email" class="label">Email address</label>
                    <input
                        type="email"
                        id="email"
                        name="email"
                        {{-- The address is preserved on failure; the password never is. --}}
                        value="{{ old('email') }}"
                        autocomplete="username"
                        required
                        autofocus
                        class="field @error('email') field-error @enderror"
                        placeholder="you@example.com"
                    >
                    <x-input-error :messages="$errors->get('email')" />
                </div>

                <div>
                    <label for="password" class="label">Password</label>

                    <div x-data="{ show: false }" class="relative">
                        <input
                            :type="show ? 'text' : 'password'"
                            type="password"
                            id="password"
                            name="password"
                            autocomplete="current-password"
                            required
                            class="field pr-11 @error('password') field-error @enderror"
                            placeholder="Your password"
                        >
                        <button
                            type="button"
                            @click="show = ! show"
                            class="absolute inset-y-0 right-0 grid w-11 place-items-center text-slate-400 transition hover:text-slate-600 dark:hover:text-slate-200"
                            :aria-label="show ? 'Hide password' : 'Show password'"
                            aria-label="Show password"
                        >
                            <svg x-show="! show" class="size-5" fill="none" viewBox="0 0 24 24" stroke-width="1.6" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                            <svg x-show="show" x-cloak class="size-5" fill="none" viewBox="0 0 24 24" stroke-width="1.6" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 001.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.894 7.894L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.242 4.242L9.88 9.88" />
                            </svg>
                        </button>
                    </div>

                    <x-input-error :messages="$errors->get('password')" />
                </div>

                <label class="flex cursor-pointer items-center gap-2.5 text-sm text-slate-600 select-none dark:text-slate-400">
                    <input
                        type="checkbox"
                        name="remember"
                        value="1"
                        @checked(old('remember'))
                        class="size-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500 dark:border-slate-600 dark:bg-slate-800"
                    >
                    Keep me logged in
                </label>

                <button type="submit"
                    class="w-full rounded-lg bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-500 focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-slate-900">
                    Log in
                </button>
            </form>

            <p class="mt-6 text-center text-sm text-slate-500 dark:text-slate-400">
                Don't have an account?
                <a href="{{ route('register') }}" class="font-semibold text-indigo-600 hover:text-indigo-500 dark:text-indigo-400">
                    Create one
                </a>
            </p>
        </div>
    </div>

</x-layout>
