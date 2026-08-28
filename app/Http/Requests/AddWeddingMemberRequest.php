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
        $wedding = $this->route('wedding');

        return [
            'email' => [
                'required_without:user_id',
                'nullable',
                'email',
                'exists:users,email',
                function ($attribute, $value, $fail) use ($wedding) {
                    if ($value && $wedding) {
                        $user = \App\Models\User::where('email', $value)->first();
                        if ($user && $wedding->members()->where('user_id', $user->id)->exists()) {
                            $fail('Este usuario ya es miembro de este espacio de boda.');
                        }
                    }
                },
            ],
            'user_id' => [
                'required_without:email',
                'nullable',
                'integer',
                'exists:users,id',
                $wedding ? 'unique:wedding_members,user_id,NULL,id,wedding_id,'.$wedding->id : '',
            ],
            'role' => ['required', 'in:editor,owner'],
        ];
    }
}
