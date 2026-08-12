<?php

namespace App\Http\Requests\OfficeServiceCategory;

use Illuminate\Foundation\Http\FormRequest;
use Bouncer;
use App\Traits\PayloadTrait;
use Illuminate\Validation\Rule;

/**
 * Store
 *
 * This request class handles validation and authorization for the request.
 * You can override the authorize() and rules() methods as needed.
 */
class Store extends FormRequest
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
    }

    public function rules(): array
    {
        $validate = [
            'type'      => ['required', 'string', 'max:255', Rule::unique('office_service_categories', 'type')->whereNull('deleted_at')],
            'office_id' => 'nullable|exists:offices,id,deleted_at,NULL',
        ];
        
        return array_merge($this->payloadTaits(), $validate);
    }
}
