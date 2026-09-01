@props([
    'idea',
    'action',
    'method' => 'POST',
    'submitLabel' => 'Save idea',
    'categories' => null,
])

@php
    use App\Enums\IdeaPriority;
    use App\Enums\IdeaStatus;
    use App\Http\Requests\IdeaRequest;
    use App\Support\UploadLimits;

    $categories ??= collect();

    // Status is only editable while there is nothing to derive it from. Once the
    // idea has checkpoints it is owned by Idea::syncStatusFromCheckpoints(),
    // and IdeaRequest discards anything submitted for it.
    $statusIsDerived = $idea->exists && $idea->hasCheckpoints();
    $coverLimit = UploadLimits::label(IdeaRequest::maxKilobytes());

    // On a validation failure `tags` comes back as the normalised array, so it
    // is flattened here to repopulate the single comma-separated field.
    $oldTags = old('tags');
    $tagValue = is_array($oldTags)
        ? implode(', ', $oldTags)
        : ($oldTags ?? implode(', ', $idea->tags ?? []));

    $existingCover = $idea->exists ? $idea->coverImageUrl() : null;
@endphp

<form
    method="POST"
    action="{{ $action }}"
    enctype="multipart/form-data"
    x-data="{
        preview: null,
        removeCover: false,
        pick(event) {
            const file = event.target.files[0];
            if (!file) return;
            this.preview = URL.createObjectURL(file);
            this.removeCover = false;
        },
    }"
    class="space-y-5"
>
    @csrf
    @if ($method !== 'POST')
        @method($method)
    @endif

    <div class="card-surface space-y-5 p-5 sm:p-6">

        <div>
            <label for="title" class="label">Title <span class="text-rose-500">*</span></label>
            <input
                type="text"
                id="title"
                name="title"
                value="{{ old('title', $idea->title) }}"
                required
                maxlength="150"
                autofocus
                class="field @error('title') field-error @enderror"
                placeholder="Give the idea a short, clear name"
            >
            <x-input-error :messages="$errors->get('title')" />
        </div>

        <div>
            <label for="description" class="label">Description <span class="text-rose-500">*</span></label>
            <textarea
                id="description"
                name="description"
                rows="5"
                required
                maxlength="5000"
                class="field @error('description') field-error @enderror"
                placeholder="What is the idea? What would done look like?"
            >{{ old('description', $idea->description) }}</textarea>
            <x-input-error :messages="$errors->get('description')" />
        </div>

        <div class="grid gap-5 sm:grid-cols-2">
            <div>
                <label for="status" class="label">Status</label>

                @if (! $idea->exists)
                    {{-- A new idea always starts as Pending; there is nothing to choose. --}}
                    <div class="flex h-[42px] items-center gap-2 rounded-lg border border-dashed border-slate-300 px-3 dark:border-slate-700">
                        <x-status-badge :status="IdeaStatus::Pending" />
                        <span class="text-xs text-slate-500 dark:text-slate-400">set automatically</span>
                    </div>
                @elseif ($statusIsDerived)
                    <div class="flex h-[42px] items-center gap-2 rounded-lg border border-dashed border-slate-300 px-3 dark:border-slate-700">
                        <x-status-badge :status="$idea->status" />
                        <span class="text-xs text-slate-500 dark:text-slate-400">from checkpoints</span>
                    </div>
                @else
                    <select id="status" name="status" class="field @error('status') field-error @enderror">
                        @foreach (IdeaStatus::options() as $value => $label)
                            <option value="{{ $value }}"
                                @selected(old('status', $idea->status?->value ?? IdeaStatus::Pending->value) === $value)>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                @endif

                <p class="mt-1.5 text-xs text-slate-500 dark:text-slate-400">
                    @if ($statusIsDerived)
                        Tracked from your checkpoints — tick them off to move it along.
                    @elseif (! $idea->exists)
                        Add checkpoints after saving and the status will track them.
                    @else
                        Add checkpoints and the status will be tracked automatically.
                    @endif
                </p>

                <x-input-error :messages="$errors->get('status')" />
            </div>

            <div>
                <label for="priority" class="label">Priority</label>
                <select id="priority" name="priority" class="field @error('priority') field-error @enderror">
                    @foreach (IdeaPriority::options() as $value => $label)
                        <option value="{{ $value }}"
                            @selected(old('priority', $idea->priority?->value ?? IdeaPriority::Medium->value) === $value)>
                            {{ $label }}
                        </option>
                    @endforeach
                </select>
                <x-input-error :messages="$errors->get('priority')" />
            </div>
        </div>

        <div class="grid gap-5 sm:grid-cols-2">
            <div>
                <label for="category" class="label">Category</label>
                <input
                    type="text"
                    id="category"
                    name="category"
                    value="{{ old('category', $idea->category) }}"
                    maxlength="60"
                    list="category-options"
                    class="field @error('category') field-error @enderror"
                    placeholder="e.g. Side project"
                >
                {{-- Suggests categories already in use, without restricting to them. --}}
                <datalist id="category-options">
                    @foreach ($categories as $category)
                        <option value="{{ $category }}"></option>
                    @endforeach
                </datalist>
                <x-input-error :messages="$errors->get('category')" />
            </div>

            <div>
                <label for="due_date" class="label">Due date</label>
                <input
                    type="date"
                    id="due_date"
                    name="due_date"
                    value="{{ old('due_date', $idea->due_date?->format('Y-m-d')) }}"
                    class="field @error('due_date') field-error @enderror"
                >
                <x-input-error :messages="$errors->get('due_date')" />
            </div>
        </div>

        <div>
            <label for="tags" class="label">Tags</label>
            <input
                type="text"
                id="tags"
                name="tags"
                value="{{ $tagValue }}"
                class="field @error('tags') field-error @enderror"
                placeholder="research, design, q3"
            >
            <p class="mt-1.5 text-xs text-slate-500 dark:text-slate-400">
                Separate tags with commas. Up to 10.
            </p>
            <x-input-error :messages="$errors->get('tags')" />
            <x-input-error :messages="$errors->get('tags.*')" />
        </div>
    </div>

    {{-- Cover image --}}
    <div class="card-surface p-5 sm:p-6">
        <label class="label">Cover image</label>
        <p class="mb-3 text-xs text-slate-500 dark:text-slate-400">
            {{-- The real limit, which is capped by PHP's upload_max_filesize. --}}
            JPG, PNG, WebP or GIF, up to {{ $coverLimit }}. Shown at the top of the idea card.
        </p>

        <div class="flex flex-col gap-4 sm:flex-row sm:items-start">

            {{-- Preview: newly picked file, else the existing cover, else a placeholder --}}
            <div class="w-full shrink-0 overflow-hidden rounded-xl border border-slate-200 bg-slate-100 sm:w-56 dark:border-slate-800 dark:bg-slate-800">
                <template x-if="preview">
                    <img :src="preview" alt="" class="aspect-[16/9] w-full object-cover">
                </template>

                @if ($existingCover)
                    <img
                        src="{{ $existingCover }}"
                        alt="Current cover image"
                        class="aspect-[16/9] w-full object-cover"
                        x-show="! preview && ! removeCover"
                    >
                @endif

                <div
                    class="grid aspect-[16/9] w-full place-items-center"
                    @if ($existingCover)
                        x-show="! preview && removeCover"
                        x-cloak
                    @else
                        x-show="! preview"
                    @endif
                >
                    <svg class="size-8 text-slate-300 dark:text-slate-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 001.5-1.5V6a1.5 1.5 0 00-1.5-1.5H3.75A1.5 1.5 0 002.25 6v12a1.5 1.5 0 001.5 1.5zm10.5-11.25h.008v.008h-.008V8.25zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z" />
                    </svg>
                </div>
            </div>

            <div class="min-w-0 flex-1 space-y-3">
                <input
                    type="file"
                    id="cover_image"
                    name="cover_image"
                    accept="image/jpeg,image/png,image/webp,image/gif"
                    @change="pick($event)"
                    class="block w-full text-sm text-slate-600 file:mr-3 file:rounded-lg file:border-0 file:bg-indigo-50 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-indigo-700 hover:file:bg-indigo-100 dark:text-slate-400 dark:file:bg-indigo-500/15 dark:file:text-indigo-300"
                >
                <x-input-error :messages="$errors->get('cover_image')" />

                @if ($existingCover)
                    <label class="flex cursor-pointer items-center gap-2.5 text-sm text-slate-600 select-none dark:text-slate-400">
                        <input
                            type="checkbox"
                            name="remove_cover_image"
                            value="1"
                            x-model="removeCover"
                            class="size-4 rounded border-slate-300 text-rose-600 focus:ring-rose-500 dark:border-slate-600 dark:bg-slate-800"
                        >
                        Remove the current cover image
                    </label>
                @endif
            </div>
        </div>
    </div>

    <div class="flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
        <a href="{{ $idea->exists ? route('ideas.show', $idea) : route('ideas.index') }}"
            class="rounded-lg border border-slate-300 px-4 py-2.5 text-center text-sm font-semibold text-slate-700 transition hover:bg-slate-50 dark:border-slate-700 dark:text-slate-200 dark:hover:bg-slate-800">
            Cancel
        </a>
        <button type="submit"
            class="rounded-lg bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-500">
            {{ $submitLabel }}
        </button>
    </div>
</form>
