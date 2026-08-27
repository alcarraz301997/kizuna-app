<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreGuestRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'name'        => ['required', 'string', 'max:255'],
            'email'       => ['nullable', 'email', 'max:255'],
            'phone'       => ['nullable', 'string', 'max:50'],
            'rsvp_status' => ['required', 'string', 'in:pendiente,confirmado,no_asiste'],
            'table_id'    => ['nullable', 'integer', 'exists:tables,id'],
        ];
    }
}
