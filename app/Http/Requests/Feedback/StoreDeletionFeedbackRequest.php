<?php

declare(strict_types=1);

namespace App\Http\Requests\Feedback;

use App\Enums\DeletionReason;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class StoreDeletionFeedbackRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'email' => ['required', 'email'],
            'reason' => ['required', Rule::enum(DeletionReason::class)],
            'comment' => ['nullable', 'string', 'max:5000'],
        ];
    }
}
