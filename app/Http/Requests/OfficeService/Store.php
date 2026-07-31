<?php

namespace App\Http\Requests\OfficeService;

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
     * Get the validation rules that apply to the request.
     * Override this method to define custom validation rules.
     *
     * @return array
     */
    public function rules(): array
    {
        // Add your validation rules here
        $validate = [
            'name'                       => ['required', Rule::unique('office_services')->whereNull('deleted_at')],
            'office_id'                  => 'required|exists:offices,id,deleted_at,NULL',
            'office_service_category_id' => 'required|exists:office_service_categories,id,deleted_at,NULL',
            'requirements'               => 'required|array',
            'requirements.*.list'        => 'required|string'
        ];

        return array_merge($this->payloadTaits(), $validate);
    }
}
