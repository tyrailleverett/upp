<?php

declare(strict_types=1);

namespace App\Http\Requests\Sites;

use App\Enums\IncidentStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class StoreIncidentRequest extends FormRequest
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
            'title' => ['required', 'string', 'max:255'],
            'status' => ['required', Rule::enum(IncidentStatus::class)],
            'message' => ['required', 'string', 'max:5000'],
            'component_ids' => ['required', 'array', 'min:1'],
            'component_ids.*' => ['required', 'integer', Rule::exists('components', 'id')->where('site_id', $this->route('site')->id)],
        ];
    }
}
