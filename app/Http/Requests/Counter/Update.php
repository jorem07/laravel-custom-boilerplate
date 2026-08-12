<?php

namespace App\Http\Requests\Counter;

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
    public function prepareForValidation(): void
    {
        $this->payloadPrepareForValidation();

        if ($this->has('service_ids') && is_array($this->service_ids) && count($this->service_ids) > 0) {
            $this->merge([
                'office_service_id' => $this->office_service_id ?? $this->service_ids[0],
            ]);
        }
    }

    public function rules(): array
    {
        $validate = [
            'name'              => ['required', 'string', 'max:255'],
            'office_service_id' => ['nullable', Rule::exists('office_services', 'id')->whereNull('deleted_at')],
            'service_ids'       => 'nullable|array',
            'service_ids.*'     => 'numeric|exists:office_services,id,deleted_at,NULL',
            'user_id'           => 'nullable|exists:users,id,deleted_at,NULL',
            'office_id'         => 'nullable',
            'status'            => 'nullable|string',
        ];   
        
        return array_merge($this->payloadTaits(), $validate);
    }
}
