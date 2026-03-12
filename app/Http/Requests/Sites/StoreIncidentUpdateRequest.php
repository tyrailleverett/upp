<?php

declare(strict_types=1);

namespace App\Http\Requests\Sites;

use App\Enums\IncidentStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class StoreIncidentUpdateRequest extends FormRequest
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
            'status' => ['required', Rule::enum(IncidentStatus::class)],
            'message' => ['required', 'string', 'max:5000'],
        ];
    }
}
