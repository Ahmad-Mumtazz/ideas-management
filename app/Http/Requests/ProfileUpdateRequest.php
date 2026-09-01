<?php

namespace App\Http\Requests;

use App\Http\Requests\Concerns\NormalizesOptionalUploads;
use App\Support\UploadLimits;
use Illuminate\Foundation\Http\FormRequest;

/**
 * The user's email is intentionally absent from these rules. Because the
 * controller only ever fills `validated()` output, an `email` field smuggled
 * into the request body is discarded rather than saved.
 */
class ProfileUpdateRequest extends FormRequest
{
    use NormalizesOptionalUploads;

    /**
     * Intended cap for a profile picture, before PHP's own limit is applied.
     */
    public const PREFERRED_KB = 2048;

    public static function maxKilobytes(): int
    {
        return UploadLimits::forFile(self::PREFERRED_KB);
    }

    /**
     * An untouched file input must not block a request that only wants to
     * remove the current picture.
     */
    protected function prepareForValidation(): void
    {
        $this->dropEmptyUpload('profile_photo');
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'min:2', 'max:255'],
            'phone' => ['nullable', 'string', 'max:40', 'regex:/^[0-9+()\-.\s]+$/'],
            'profile_photo' => [
                'nullable',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:'.self::maxKilobytes(),
            ],
            'remove_profile_photo' => ['nullable', 'boolean'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        $limit = UploadLimits::label(self::maxKilobytes());

        return [
            'phone.regex' => 'The contact number may only contain digits, spaces and + ( ) - . characters.',
            'profile_photo.max' => "The profile picture may not be larger than {$limit}.",
            'profile_photo.mimes' => 'The profile picture must be a JPG, PNG or WebP image.',
            // Raised by PHP itself when the file exceeds upload_max_filesize,
            // before any of the rules above get a chance to run.
            'profile_photo.uploaded' => "The profile picture could not be uploaded. It must be an image no larger than {$limit}.",
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'phone' => 'contact number',
            'profile_photo' => 'profile picture',
        ];
    }
}
