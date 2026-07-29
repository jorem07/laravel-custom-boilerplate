<?php

namespace App\Http\Requests\PDF;

use App\Traits\PayloadTrait;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\DB;

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
            'uuid'          => 'required_without:id|uuid|exists:queues,uuid',
            'disposition'   => 'nullable|in:inline,download',
            'is_not_expired' => 'accepted'
        ];
    }

    public function messages()
    {
        return [
            'is_not_expired.accepted'    => 'The queue has been expired.'
        ];
    }

    public function prepareForValidation(): void
    {
        $this->merge([
            'is_not_expired'    => DB::table('queues')->where('uuid', $this->uuid)->whereDate('created_at', '>=', \Carbon\Carbon::now())->exists()
        ]);
    }
}
