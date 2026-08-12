<?php

namespace App\Http\Requests\Queue;

use App\Traits\PayloadTrait;
use Illuminate\Foundation\Http\FormRequest;

use Illuminate\Validation\Rule;

class Transfer extends FormRequest
{
    use PayloadTrait;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'queue_id'          => ['required', Rule::exists('queues', 'id')->whereNull('deleted_at')],
            'target_counter_id' => ['required', Rule::exists('counters', 'id')->whereNull('deleted_at')],
            'target_counter_name' => 'nullable|string',
            'reason'            => 'nullable|string',
        ];
    }
}
