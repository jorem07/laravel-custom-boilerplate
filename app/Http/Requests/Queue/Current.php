<?php

namespace App\Http\Requests\Queue;

use App\Traits\PayloadTrait;
use Illuminate\Foundation\Http\FormRequest;

class Current extends FormRequest
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
        return [];
    }

    public function prepareForValidation(): void
    {
        $this->payloadPrepareForValidation();
    }
}
