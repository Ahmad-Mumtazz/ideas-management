<x-layout title="Profile">

    <div class="mb-6">
        <h1 class="text-2xl font-bold tracking-tight text-slate-900 sm:text-3xl dark:text-white">
            Your profile
        </h1>
        <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
            Update your name, contact details and picture.
        </p>
    </div>

    <div class="grid gap-6 lg:grid-cols-3">

        <div class="lg:col-span-2">
            <form
                method="POST"
                action="{{ route('profile.update') }}"
                enctype="multipart/form-data"
                x-data="{
                    preview: null,
                    removePhoto: false,
                    pick(event) {
                        const file = event.target.files[0];
                        if (!file) return;
                        this.preview = URL.createObjectURL(file);
                        this.removePhoto = false;
                    },
                }"
                class="space-y-5">
                @csrf
                @method('PATCH')

                {{-- Picture --}}
                <div class="card-surface p-5 sm:p-6">
                    <label class="label">Profile picture</label>
                    <p class="mb-4 text-xs text-slate-500 dark:text-slate-400">
                        {{-- The real limit, which is capped by PHP's upload_max_filesize. --}}
                        JPG, PNG or WebP, up to {{ \App\Support\UploadLimits::label(\App\Http\Requests\ProfileUpdateRequest::maxKilobytes()) }}.
                    </p>

                    <div class="flex flex-col gap-4 sm:flex-row sm:items-center">
                        <div class="shrink-0">
                            <template x-if="preview">
                                <img :src="preview" alt="" class="size-20 rounded-full object-cover ring-1 ring-slate-200 dark:ring-slate-700">
                            </template>

                            <div x-show="! preview">
                                @if ($user->profile_photo)
                                <img src="{{ $user->profilePhotoUrl() }}" alt="{{ $user->name }}"
                                    class="size-20 rounded-full object-cover ring-1 ring-slate-200 dark:ring-slate-700"
                                    x-show="! removePhoto">
                                <span x-show="removePhoto" x-cloak
                                    class="grid size-20 place-items-center rounded-full bg-gradient-to-br from-indigo-500 to-violet-600 text-xl font-semibold text-white">
                                    {{ $user->initials }}
                                </span>
                                @else
                                <span class="grid size-20 place-items-center rounded-full bg-gradient-to-br from-indigo-500 to-violet-600 text-xl font-semibold text-white">
                                    {{ $user->initials }}
                                </span>
                                @endif
                            </div>
                        </div>

                        <div class="min-w-0 flex-1 space-y-3">
                            <input
                                type="file"
                                id="profile_photo"
                                name="profile_photo"
                                accept="image/jpeg,image/png,image/webp"
                                @change="pick($event)"
                                class="block w-full text-sm text-slate-600 file:mr-3 file:rounded-lg file:border-0 file:bg-indigo-50 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-indigo-700 hover:file:bg-indigo-100 dark:text-slate-400 dark:file:bg-indigo-500/15 dark:file:text-indigo-300">
                            <x-input-error :messages="$errors->get('profile_photo')" />

                            @if ($user->profile_photo)
                            <label class="flex cursor-pointer items-center gap-2.5 text-sm text-slate-600 select-none dark:text-slate-400">
                                <input type="checkbox" name="remove_profile_photo" value="1" x-model="removePhoto"
                                    class="size-4 rounded border-slate-300 text-rose-600 focus:ring-rose-500 dark:border-slate-600 dark:bg-slate-800">
                                Remove my current picture
                            </label>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Details --}}
                <div class="card-surface space-y-5 p-5 sm:p-6">
                    <div>
                        <label for="name" class="label">Name <span class="text-rose-500">*</span></label>
                        <input type="text" id="name" name="name" value="{{ old('name', $user->name) }}" required
                            maxlength="255" autocomplete="name"
                            class="field @error('name') field-error @enderror">
                        <x-input-error :messages="$errors->get('name')" />
                    </div>

                    <div>
                        <label for="phone" class="label">Contact number</label>
                        <input type="tel" id="phone" name="phone" value="{{ old('phone', $user->phone) }}"
                            maxlength="40" autocomplete="tel"
                            placeholder="+92 300 7000000"
                            class="field @error('phone') field-error @enderror">
                        <x-input-error :messages="$errors->get('phone')" />
                    </div>

                    <div>
                        <label for="email_display" class="label">Email address</label>
                        {{--
                            Displayed for reference only. There is no `email` field
                            in this form, and ProfileUpdateRequest does not validate
                            one, so it cannot be changed from here or by editing the
                            request by hand.
                        --}}
                        <input type="email" id="email_display" value="{{ $user->email }}" disabled readonly
                            class="field cursor-not-allowed bg-slate-100 text-slate-500 dark:bg-slate-800 dark:text-slate-400">
                        <p class="mt-1.5 flex items-start gap-1.5 text-xs text-slate-500 dark:text-slate-400">
                            <svg class="mt-px size-3.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z" />
                            </svg>
                            Your email address is fixed and cannot be changed.
                        </p>
                    </div>
                </div>

                <div class="flex justify-end">
                    <button type="submit"
                        class="w-full rounded-lg bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-500 sm:w-auto">
                        Save changes
                    </button>
                </div>
            </form>
        </div>

        {{-- Summary --}}
        <div class="space-y-6">
            <div class="card-surface p-5 sm:p-6">
                <div class="flex items-center gap-3">
                    <x-avatar :user="$user" class="size-12 text-base" />
                    <div class="min-w-0">
                        <p class="truncate font-semibold text-slate-900 dark:text-white">{{ $user->name }}</p>
                        <p class="truncate text-sm text-slate-500 dark:text-slate-400">{{ $user->email }}</p>
                    </div>
                </div>

                <dl class="mt-5 space-y-3 border-t border-slate-100 pt-5 text-sm dark:border-slate-800">
                    <div class="flex items-center justify-between gap-3">
                        <dt class="text-slate-500 dark:text-slate-400">Ideas</dt>
                        <dd class="font-semibold tabular-nums text-slate-900 dark:text-white">{{ $ideaCount }}</dd>
                    </div>
                    <div class="flex items-center justify-between gap-3">
                        <dt class="text-slate-500 dark:text-slate-400">Member since</dt>
                        <dd class="font-medium text-slate-900 dark:text-white">{{ $user->created_at->format('M Y') }}</dd>
                    </div>
                </dl>
            </div>

            <div class="card-surface p-5 sm:p-6">
                <h2 class="text-sm font-semibold text-slate-900 dark:text-white">Session</h2>
                <p class="mt-1.5 text-xs text-slate-500 dark:text-slate-400">
                    Logging out ends this session everywhere on this device.
                </p>
                <form method="POST" action="{{ route('logout') }}" class="mt-4">
                    @csrf
                    @method('DELETE')
                    <button type="submit"
                        class="w-full rounded-lg border border-rose-300 px-4 py-2.5 text-sm font-semibold text-rose-700 transition hover:bg-rose-50 dark:border-rose-500/40 dark:text-rose-400 dark:hover:bg-rose-500/10">
                        Log out
                    </button>
                </form>
            </div>

            <div class="card-surface p-5 sm:p-6">
                <h2 class="text-sm font-semibold text-slate-900 dark:text-white">
                    Change password
                </h2>

                <p class="mt-1.5 text-xs text-slate-500 dark:text-slate-400">
                    Update your password to keep your account secure.
                </p>

                <form
                    method="POST"
                    action="{{ route('profile.password.update') }}"
                    class="mt-4 space-y-4">
                    @csrf
                    @method('PATCH')

                    <div>
                        <label for="current_password" class="label">
                            Current password
                        </label>

                        <input
                            type="password"
                            id="current_password"
                            name="current_password"
                            required
                            autocomplete="current-password"
                            class="field @error('current_password') field-error @enderror">

                        <x-input-error :messages="$errors->get('current_password')" />
                    </div>

                    <div>
                        <label for="password" class="label">
                            New password
                        </label>

                        <input
                            type="password"
                            id="password"
                            name="password"
                            required
                            autocomplete="new-password"
                            class="field @error('password') field-error @enderror">

                        <x-input-error :messages="$errors->get('password')" />
                    </div>

                    <div>
                        <label for="password_confirmation" class="label">
                            Confirm new password
                        </label>

                        <input
                            type="password"
                            id="password_confirmation"
                            name="password_confirmation"
                            required
                            autocomplete="new-password"
                            class="field">
                    </div>

                    <div class="flex justify-end">
                        <button
                            type="submit"
                            class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">
                            Change password
                        </button>
                    </div>
                </form>
            </div>


            <div class="card-surface border-rose-200 p-5 sm:p-6 dark:border-rose-500/30">
                <h2 class="text-sm font-semibold text-rose-700 dark:text-rose-400">
                    Danger zone
                </h2>

                <p class="mt-1.5 text-xs text-slate-500 dark:text-slate-400">
                    Permanently delete your account and all of your ideas and uploaded files.
                    This action cannot be undone.
                </p>

                <form
                    method="POST"
                    action="{{ route('profile.destroy') }}"
                    class="mt-4 space-y-3"
                    onsubmit="return confirm('Are you sure you want to permanently delete your account? This cannot be undone.');">
                    @csrf
                    @method('DELETE')

                    <div>
                        <label for="current_password" class="label">
                            Current password
                        </label>

                        <input
                            type="password"
                            id="current_password"
                            name="current_password"
                            required
                            autocomplete="current-password"
                            class="field @error('current_password') field-error @enderror"
                            placeholder="Enter your current password">

                        <x-input-error :messages="$errors->get('current_password')" />
                    </div>

                    <button
                        type="submit"
                        class="w-full rounded-lg bg-rose-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-rose-500">
                        Delete my account
                    </button>
                </form>
            </div>
        </div>
    </div>

</x-layout>