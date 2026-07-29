<?php

namespace App\Http\Requests\OfficeService;

use Illuminate\Foundation\Http\FormRequest;
use Bouncer;
use App\Traits\PayloadTrait;

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
    public function rules(): array
    {
        // Add your validation rules here
        $validate = [
            'name'                       => 'required|unique:office_services,name,NULL,NULL,deleted_at,NULL',
            'office_id'                  => 'required|exists:offices,id,deleted_at,NULL',
            'office_service_category_id' => 'required|exists:office_service_categories,id,deleted_at,NULL'
        ];

        return array_merge($this->payloadTaits(), $validate);
    }
}
