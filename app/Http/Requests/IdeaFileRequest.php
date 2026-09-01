<?php

namespace App\Http\Requests;

use App\Support\UploadLimits;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Validation for uploading documents against an idea.
 */
class IdeaFileRequest extends FormRequest
{
    /**
     * Extensions accepted for idea attachments. Deliberately excludes anything
     * executable or server-interpretable.
     */
    public const ALLOWED = 'pdf,doc,docx,xls,xlsx,ppt,pptx,txt,csv,md,rtf,odt,ods,zip,jpg,jpeg,png,webp,gif,svg';

    /**
     * Intended cap per file, before PHP's own limit is applied.
     */
    public const PREFERRED_KB = 10240;

    public static function maxKilobytes(): int
    {
        return UploadLimits::forFile(self::PREFERRED_KB);
    }

    /**
     * How many files can be sent at once without the request body exceeding
     * `post_max_size` — which would empty $_POST and surface as a confusing
     * "page expired" rather than a validation error.
     */
    public static function maxFiles(): int
    {
        $perRequest = (int) floor(UploadLimits::postMaxKilobytes() / max(self::maxKilobytes(), 1));

        return max(1, min(10, $perRequest));
    }

    /**
     * Runs before the rules, so an upload aimed at another user's idea is
     * rejected with a 403 before any file is processed or written to disk.
     */
    public function authorize(): bool
    {
        $idea = $this->route('idea');

        return $idea !== null && $this->user()->can('update', $idea);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'files' => ['required', 'array', 'max:'.self::maxFiles()],
            'files.*' => ['file', 'mimes:'.self::ALLOWED, 'max:'.self::maxKilobytes()],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        $limit = UploadLimits::label(self::maxKilobytes());
        $count = self::maxFiles();

        return [
            'files.required' => 'Choose at least one file to upload.',
            'files.max' => "You can upload up to {$count} files at a time.",
            'files.*.mimes' => 'That file type is not allowed.',
            'files.*.max' => "Each file may not be larger than {$limit}.",
            'files.*.uploaded' => "That file could not be uploaded. Each file must be no larger than {$limit}.",
        ];
    }
}
