<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ExpenseCommitmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'planned_amount' => ['sometimes', 'nullable', 'numeric', 'gte:0'],
            'contracted_amount' => ['sometimes', 'nullable', 'numeric', 'gte:0'],
            'due_date' => ['sometimes', 'nullable', 'date'],
        ];
    }
}
