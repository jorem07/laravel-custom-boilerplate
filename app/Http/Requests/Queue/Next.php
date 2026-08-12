<?php

namespace App\Http\Requests\Queue;

use App\Traits\PayloadTrait;
use Illuminate\Foundation\Http\FormRequest;

use Illuminate\Validation\Rule;

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
            'counter_id'  => ['required', Rule::exists('counters', 'id')->whereNull('deleted_at')],
            'user_id'     => ['nullable', Rule::exists('users', 'id')->whereNull('deleted_at')],
            'service_ids' => ['nullable', 'array'],
        ];
    }

    public function prepareForValidation(): void
    {
        $this->payloadPrepareForValidation();
    }
}
