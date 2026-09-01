<x-layout title="Create account">

    <div class="flex min-h-[calc(100vh-14rem)] items-center justify-center px-2 py-8">
        <div class="w-full max-w-md">

            <div class="mb-7 text-center">
                <h1 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-white">
                    Create your account
                </h1>
                <p class="mt-1.5 text-sm text-slate-500 dark:text-slate-400">
                    Start capturing and tracking your ideas.
                </p>
            </div>

            <form method="POST" action="{{ route('register') }}" class="card-surface space-y-5 p-6 sm:p-7">
                @csrf

                <div>
                    <label for="name" class="label">Name</label>
                    <input
                        type="text"
                        id="name"
                        name="name"
                        value="{{ old('name') }}"
                        autocomplete="name"
                        required
                        autofocus
                        class="field @error('name') field-error @enderror"
                        placeholder="Your name"
                    >
                    <x-input-error :messages="$errors->get('name')" />
                </div>

                <div>
                    <label for="email" class="label">Email address</label>
                    <input
                        type="email"
                        id="email"
                        name="email"
                        value="{{ old('email') }}"
                        autocomplete="email"
                        required
                        class="field @error('email') field-error @enderror"
                        placeholder="you@example.com"
                    >
                    <x-input-error :messages="$errors->get('email')" />
                    <p class="mt-1.5 text-xs text-slate-500 dark:text-slate-400">
                        This cannot be changed later, so use an address you'll keep.
                    </p>
                </div>

                <div>
                    <label for="password" class="label">Password</label>
                    <input
                        type="password"
                        id="password"
                        name="password"
                        autocomplete="new-password"
                        required
                        class="field @error('password') field-error @enderror"
                        placeholder="Create a strong password"
                    >
                    <x-input-error :messages="$errors->get('password')" />
                </div>

                <div>
                    <label for="password_confirmation" class="label">Confirm password</label>
                    <input
                        type="password"
                        id="password_confirmation"
                        name="password_confirmation"
                        autocomplete="new-password"
                        required
                        class="field"
                        placeholder="Repeat your password"
                    >
                </div>

                <button type="submit"
                    class="w-full rounded-lg bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-500 focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-slate-900">
                    Create account
                </button>
            </form>

            <p class="mt-6 text-center text-sm text-slate-500 dark:text-slate-400">
                Already have an account?
                <a href="{{ route('login') }}" class="font-semibold text-indigo-600 hover:text-indigo-500 dark:text-indigo-400">
                    Log in
                </a>
            </p>
        </div>
    </div>

</x-layout>
