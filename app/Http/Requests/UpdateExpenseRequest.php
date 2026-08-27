<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateExpenseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'category_id' => ['required', 'exists:categories,id'],
            'amount'      => ['required', 'numeric', 'gt:0'],
            'vendor'      => ['nullable', 'string', 'max:255'],
            'vendor_id'   => ['nullable', 'exists:vendors,id'],
            'status'      => ['required', 'string', 'in:planned,contracted,paid'],
            'paid_date'   => ['nullable', 'date'],
            'notes'       => ['nullable', 'string'],
        ];
    }
}
