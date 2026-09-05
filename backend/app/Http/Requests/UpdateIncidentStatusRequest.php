<?php

namespace App\Http\Requests;

use App\Enums\IncidentStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateIncidentStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'status' => ['required', Rule::in(array_column(IncidentStatus::cases(), 'value'))],
        ];
    }

    public function messages(): array
    {
        return [
            'status.required' => 'O campo status e obrigatorio.',
            'status.in' => 'O campo status deve ser um dos valores permitidos.',
        ];
    }
}
