<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AddWeddingMemberRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null
            && $this->route('wedding')->members()
                ->where('user_id', $this->user()->id)
                ->where('role', 'owner')
                ->exists();
    }

    public function rules(): array
    {
        return [
            'user_id' => ['required', 'integer', 'exists:users,id', 'unique:wedding_members,user_id,NULL,id,wedding_id,'.$this->route('wedding')->id],
            'role' => ['required', 'in:editor'],
        ];
    }
}
