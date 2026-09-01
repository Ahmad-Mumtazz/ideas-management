<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Validation for adding and renaming checkpoints.
 */
class CheckpointRequest extends FormRequest
{
    /**
     * Runs before the rules: storing checks the parent idea, updating checks the
     * checkpoint (whose policy defers to that idea's owner).
     */
    public function authorize(): bool
    {
        $target = $this->route('checkpoint') ?? $this->route('idea');

        return $target !== null && $this->user()->can('update', $target);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'min:2', 'max:200'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'title' => 'checkpoint',
        ];
    }
}
