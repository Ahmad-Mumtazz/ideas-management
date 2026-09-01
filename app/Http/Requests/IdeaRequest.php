<?php

namespace App\Http\Requests;

use App\Enums\IdeaPriority;
use App\Enums\IdeaStatus;
use App\Http\Requests\Concerns\NormalizesOptionalUploads;
use App\Models\Idea;
use App\Support\UploadLimits;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

/**
 * Shared validation for creating and updating an idea.
 */
class IdeaRequest extends FormRequest
{
    use NormalizesOptionalUploads;

    /**
     * Intended cap for a cover image, before PHP's own limit is applied.
     */
    public const PREFERRED_KB = 4096;

    public static function maxKilobytes(): int
    {
        return UploadLimits::forFile(self::PREFERRED_KB);
    }

    /**
     * Checked before the rules run, so a request aimed at somebody else's idea
     * is rejected with a 403 without ever being validated. The controller
     * repeats this check as a second layer.
     */
    public function authorize(): bool
    {
        $idea = $this->route('idea');

        return $idea
            ? $this->user()->can('update', $idea)
            : $this->user()->can('create', Idea::class);
    }

    /**
     * Tags arrive as a single comma-separated field but are stored as JSON, so
     * they are normalised to a clean, de-duplicated array before validation.
     */
    protected function prepareForValidation(): void
    {
        // An untouched file input must not block a request that only wants to
        // remove the current cover image.
        $this->dropEmptyUpload('cover_image');

        if ($this->has('tags') && ! is_array($this->input('tags'))) {
            $this->merge([
                'tags' => Str::of((string) $this->input('tags'))
                    ->explode(',')
                    ->map(fn (string $tag) => trim($tag))
                    ->filter()
                    ->unique()
                    ->values()
                    ->all(),
            ]);
        }

        if ($this->filled('category')) {
            $this->merge(['category' => trim((string) $this->input('category'))]);
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'min:3', 'max:150'],
            'description' => ['required', 'string', 'min:3', 'max:5000'],
            // Absent on the create form (a new idea is always Pending) and on
            // the edit form once the idea has checkpoints to derive it from.
            'status' => ['sometimes', 'required', Rule::enum(IdeaStatus::class)],
            'priority' => ['required', Rule::enum(IdeaPriority::class)],
            'category' => ['nullable', 'string', 'max:60'],
            'tags' => ['nullable', 'array', 'max:10'],
            'tags.*' => ['string', 'max:30'],
            'due_date' => ['nullable', 'date'],

            'cover_image' => [
                'nullable',
                'image',
                'mimes:jpg,jpeg,png,webp,gif',
                'max:'.self::maxKilobytes(),
            ],
            'remove_cover_image' => ['nullable', 'boolean'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        $limit = UploadLimits::label(self::maxKilobytes());

        return [
            'cover_image.image' => 'The cover must be an image file.',
            'cover_image.mimes' => 'The cover must be a JPG, PNG, WebP or GIF image.',
            'cover_image.max' => "The cover image may not be larger than {$limit}.",
            // Raised by PHP itself when the file exceeds upload_max_filesize,
            // before any of the rules above get a chance to run.
            'cover_image.uploaded' => "The cover image could not be uploaded. It must be an image no larger than {$limit}.",
            'tags.max' => 'You can add up to 10 tags.',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'due_date' => 'due date',
            'cover_image' => 'cover image',
        ];
    }

    /**
     * The persisted attributes only — file handling is done in the controller.
     *
     * @return array<string, mixed>
     */
    public function ideaAttributes(): array
    {
        $attributes = [
            'title' => $this->validated('title'),
            'description' => $this->validated('description'),
            'priority' => $this->validated('priority'),
            'category' => $this->validated('category') ?: null,
            'tags' => $this->validated('tags') ?: null,
            'due_date' => $this->validated('due_date') ?: null,
        ];

        $idea = $this->route('idea');

        if (! $idea instanceof Idea) {
            // A brand new idea always starts as Pending — it has no completed
            // checkpoints yet, by definition.
            $attributes['status'] = IdeaStatus::Pending;
        } elseif (! $idea->hasCheckpoints()) {
            // With nothing to derive from, the owner keeps manual control.
            $attributes['status'] = $this->validated('status', $idea->status->value);
        }

        // Otherwise `status` is deliberately omitted: it is owned by
        // Idea::syncStatusFromCheckpoints() and a submitted value is discarded
        // rather than trusted. The form disables the control too, but this is
        // the server-side enforcement point.

        return $attributes;
    }
}
