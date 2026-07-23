<?php

namespace App\Http\Requests\Counter;

use App\Traits\PayloadTrait;
use Illuminate\Foundation\Http\FormRequest;

class Performance extends FormRequest
{
    use PayloadTrait {
        PayloadTrait::prepareForValidation as payloadPrepareForValidation;
    }

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'office_service_id' => 'nullable|exists:office_services,id,deleted_at,NULL',
            'limit' => 'nullable|integer|min:1|max:20',
        ];
    }
}
