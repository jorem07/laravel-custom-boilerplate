<?php

namespace App\Http\Requests\OfficeServiceCategory;

use Illuminate\Foundation\Http\FormRequest;
use Bouncer;
use App\Traits\PayloadTrait;

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
            'type'  => 'required|unique:office_service_categories,type,' . $this->id . ',id,deleted_at,NULL'
        ];
        
        return array_merge($this->payloadTaits(), $validate);
    }
}
