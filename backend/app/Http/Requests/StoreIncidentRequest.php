<?php

namespace App\Http\Requests;

use App\Enums\IncidentSeverity;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreIncidentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'min:5', 'max:150'],
            'description' => ['required', 'string', 'min:10'],
            'severity' => ['required', Rule::in(array_column(IncidentSeverity::cases(), 'value'))],
            'affected_systems' => ['required', 'array', 'min:1'],
            'affected_systems.*' => ['required', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => 'O campo title e obrigatorio.',
            'title.min' => 'O campo title deve ter no minimo 5 caracteres.',
            'title.max' => 'O campo title deve ter no maximo 150 caracteres.',
            'description.required' => 'O campo description e obrigatorio.',
            'description.min' => 'O campo description deve ter no minimo 10 caracteres.',
            'severity.required' => 'O campo severity e obrigatorio.',
            'severity.in' => 'O campo severity deve ser um dos valores permitidos.',
            'affected_systems.required' => 'Informe ao menos um sistema afetado.',
            'affected_systems.min' => 'Informe ao menos um sistema afetado.',
            'affected_systems.*.required' => 'Cada sistema afetado deve possuir um nome.',
            'affected_systems.*.string' => 'Cada sistema afetado deve ser um texto valido.',
        ];
    }
}
