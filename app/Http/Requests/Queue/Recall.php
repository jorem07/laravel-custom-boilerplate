<?php

namespace App\Http\Requests\Queue;

use App\Traits\PayloadTrait;
use Illuminate\Foundation\Http\FormRequest;

use Illuminate\Validation\Rule;

class Recall extends FormRequest
{
    use PayloadTrait;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'counter_id'   => ['required', Rule::exists('counters', 'id')->whereNull('deleted_at')],
            'queue_id'     => ['required', Rule::exists('queues', 'id')->whereNull('deleted_at')],
            'resume'       => 'nullable|boolean',
            'from_pending' => 'nullable|boolean',
            'mode'         => 'nullable|string|in:immediate,requeue,restore_pending,restore_waiting',
        ];
    }
}
