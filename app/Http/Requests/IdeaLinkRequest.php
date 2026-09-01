<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Validation for adding and editing an idea's reference links.
 */
class IdeaLinkRequest extends FormRequest
{
    /**
     * Runs before the rules: storing checks the parent idea, updating checks the
     * link (whose policy defers to that idea's owner).
     */
    public function authorize(): bool
    {
        $target = $this->route('link') ?? $this->route('idea');

        return $target !== null && $this->user()->can('update', $target);
    }

    /**
     * Accept "example.com" as a convenience and normalise it to a real URL so
     * the `url` rule has something valid to validate.
     */
    protected function prepareForValidation(): void
    {
        $url = trim((string) $this->input('url'));

        if ($url !== '' && ! preg_match('#^[a-z][a-z0-9+.-]*://#i', $url)) {
            $this->merge(['url' => 'https://'.$url]);
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'label' => ['required', 'string', 'min:2', 'max:100'],
            // Restricted to http/https so javascript: and data: URLs cannot be stored.
            'url' => ['required', 'url:http,https', 'max:2048'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'url.url' => 'Enter a valid http or https web address.',
        ];
    }
}
