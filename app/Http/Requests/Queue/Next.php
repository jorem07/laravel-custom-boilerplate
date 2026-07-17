<?php

namespace App\Http\Requests\Queue;

use App\Traits\PayloadTrait;
use Illuminate\Foundation\Http\FormRequest;

class Next extends FormRequest
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
            'counter_id' => 'required|exists:counters,id,deleted_at,NULL',
            'user_id'    => 'nullable|exists:users,id,deleted_at,NULL',
        ];
    }

    public function prepareForValidation(): void
    {
        $this->payloadPrepareForValidation();
    }
}
