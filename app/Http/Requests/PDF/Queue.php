<?php

namespace App\Http\Requests\PDF;

use App\Traits\PayloadTrait;
use Illuminate\Foundation\Http\FormRequest;

class Queue extends FormRequest
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
            // 'id' => 'required_without:uuid|integer|exists:queues,id,deleted_at,NULL',
            'uuid' => 'required_without:id|uuid|exists:queues,uuid',
            'disposition' => 'nullable|in:inline,download',
        ];
    }

    public function prepareForValidation(): void
    {
        $this->payloadPrepareForValidation();
    }
}
