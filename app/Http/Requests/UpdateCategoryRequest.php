<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        $weddingId  = $this->route('wedding')?->id;
        $categoryId = $this->route('category')?->id;

        return [
            'name'         => ['required', 'string', 'max:255', 'unique:categories,name,' . $categoryId . ',id,wedding_id,' . $weddingId],
            'budget_limit' => ['required', 'numeric', 'gt:0'],
            'color'        => ['required', 'string', 'regex:/^#[0-9a-fA-F]{6}$/'],
        ];
    }
}
