<?php

declare(strict_types=1);

namespace App\Http\Requests\Support;

use App\Enums\TicketTopic;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class StoreSupportTicketRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasVerifiedEmail() ?? false;
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string', 'max:5000'],
            'topic' => ['required', Rule::enum(TicketTopic::class)],
        ];
    }
}
