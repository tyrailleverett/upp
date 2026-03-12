<?php

declare(strict_types=1);

namespace App\Http\Requests\Sites;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class UpdateComponentRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:255', Rule::unique('components', 'name')->where('site_id', $this->route('site')->id)->ignore($this->route('component'))],
            'description' => ['nullable', 'string', 'max:1000'],
            'group' => ['nullable', 'string', 'max:255'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ];
    }
}
