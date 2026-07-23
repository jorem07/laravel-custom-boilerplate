<?php

namespace App\Http\Requests\Queue;

use Illuminate\Foundation\Http\FormRequest;
use Bouncer;
use App\Traits\PayloadTrait;
use Illuminate\Validation\Rule;

/**
 * Update
 *
 * This request class handles validation and authorization for the request.
 * You can override the authorize() and rules() methods as needed.
 */
class Update extends FormRequest
{
    use PayloadTrait {
        PayloadTrait::prepareForValidation as payloadPrepareForValidation;
    }

    /**
     * Determine if the user is authorized to make this request.
     * Override this method to implement custom authorization logic.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     * Override this method to define custom validation rules.
     *
     * @return array
     */
    public function rules(): array
    {
        // Add your validation rules here
        $validate = [
            'queue_status_id'   => 'required|exists:queue_statuses,id,deleted_at,NULL',
            'counter_id'        => 'required:exists:counters,id,deleted_at,NULL',
            'user_id'           => 'required'
        ];
        
        return array_merge($this->payloadTaits(), $validate);
    }
}
