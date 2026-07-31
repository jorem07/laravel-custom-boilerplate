<?php

namespace App\Http\Requests\OfficeService;

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
            'name'                       => ['required', Rule::unique('office_services')->ignore($this->id)->whereNull('deleted_at')],
            'office_id'                  => 'required|exists:offices,id,deleted_at,NULL',
            'office_service_category_id' => 'required|exists:office_service_categories,id,deleted_at,NULL',
            'requirements'               => 'required|array',
            'requirements.*.id'          => ['nullable', 'numeric', Rule::exists('office_service_requirements', 'id')->whereNull('deleted_at')],
            'requirements.*.list'        => 'required|string'
        ];
        
        return array_merge($this->payloadTaits(), $validate);
    }
}
