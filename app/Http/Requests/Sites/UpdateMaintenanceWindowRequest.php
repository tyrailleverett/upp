<?php

declare(strict_types=1);

namespace App\Http\Requests\Sites;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class UpdateMaintenanceWindowRequest extends FormRequest
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
            'description' => ['nullable', 'string', 'max:5000'],
            'scheduled_at' => ['required', 'date'],
            'ends_at' => ['required', 'date', 'after:scheduled_at'],
            'component_ids' => ['required', 'array', 'min:1'],
            'component_ids.*' => ['required', 'integer', Rule::exists('components', 'id')->where('site_id', $this->route('site')->id)],
        ];
    }
}
