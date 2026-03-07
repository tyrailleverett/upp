<?php

declare(strict_types=1);

namespace App\Http\Requests\Settings;

use App\Models\SocialAccount;
use Illuminate\Foundation\Http\FormRequest;

final class DisconnectSocialAccountRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        /** @var \App\Models\User $user */
        $user = $this->user();

        /** @var SocialAccount $socialAccount */
        $socialAccount = $this->route('socialAccount');

        if ($socialAccount->user_id !== $user->id) {
            return false;
        }

        if (! $user->hasPassword() && $user->socialAccounts()->count() <= 1) {
            return false;
        }

        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [];
    }
}
