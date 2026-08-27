<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        $weddingId = $this->route('wedding')?->id;

        return [
            'name'         => ['required', 'string', 'max:255', 'unique:categories,name,NULL,id,wedding_id,' . $weddingId],
            'budget_limit' => ['required', 'numeric', 'gt:0'],
            'color'        => ['required', 'string', 'regex:/^#[0-9a-fA-F]{6}$/'],
        ];
    }
}
