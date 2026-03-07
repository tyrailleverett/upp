<?php

declare(strict_types=1);

namespace App\Http\Requests\Settings;

use Closure;
use Illuminate\Foundation\Http\FormRequest;

final class DeleteAccountRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        /** @var \App\Models\User $user */
        $user = $this->user();

        return [
            'email' => [
                'required',
                'string',
                function (string $attribute, mixed $value, Closure $fail) use ($user): void {
                    if (mb_strtolower($value) !== mb_strtolower($user->email)) {
                        $fail(__('The :attribute does not match your account email.', ['attribute' => $attribute]));
                    }
                },
            ],
            'confirm' => ['required', 'accepted'],
        ];
    }
}
