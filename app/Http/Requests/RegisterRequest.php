<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class RegisterRequest extends FormRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'min:2', 'max:255'],
            'email' => ['required', 'string', 'email:rfc,dns', 'max:255', 'unique:users,email'],   
            // Complexity rules belong here, on account creation — not on login.
            'password' => ['required', 'confirmed', Password::defaults()],
            // 'password' => [
            //     'required',
            //     'confirmed',
            //     Password::min(8)
            //         ->mixedCase()
            //         ->numbers()
            //         ->symbols(),
            //     ],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'email.unique' => 'An account already exists for that email address.',
            'password.confirmed' => 'The password confirmation does not match.',
        ];
    }
}
