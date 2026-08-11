<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ExpensePaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'amount' => ['required', 'numeric', 'gte:0'],
            'paid_on' => ['nullable', 'date'],
            'kind' => ['required', 'string', 'in:deposit,payment'],
        ];
    }
}
