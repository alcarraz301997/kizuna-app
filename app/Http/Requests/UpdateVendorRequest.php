<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateVendorRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        $weddingId = $this->route('wedding')?->id;
        $vendorId  = $this->route('vendor')?->id;

        return [
            'name'             => ['required', 'string', 'max:255', 'unique:vendors,name,' . $vendorId . ',id,wedding_id,' . $weddingId],
            'service_category' => ['required', 'string', 'max:255'],
            'contact_phone'    => ['nullable', 'string', 'max:50'],
            'contact_email'    => ['nullable', 'email', 'max:255'],
            'payment_status'   => ['required', 'string', 'in:no_iniciado,pagado_parcialmente,pagado_completo'],
            'notes'            => ['nullable', 'string'],
        ];
    }
}
