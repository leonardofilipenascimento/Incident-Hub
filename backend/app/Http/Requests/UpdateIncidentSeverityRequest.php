<?php

namespace App\Http\Requests;

use App\Enums\IncidentSeverity;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateIncidentSeverityRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'severity' => ['required', Rule::in(array_column(IncidentSeverity::cases(), 'value'))],
            'comment' => ['nullable', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'severity.required' => 'O campo severity e obrigatorio.',
            'severity.in' => 'O campo severity deve ser um dos valores permitidos.',
        ];
    }
}
