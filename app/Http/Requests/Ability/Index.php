<?php

namespace App\Http\Requests\Ability;

use App\Traits\PayloadTrait;
use Bouncer;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Index
 *
 * This request class handles validation and authorization for the request.
 * You can override the authorize() and rules() methods as needed.
 */
class Index extends FormRequest
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
        $validate = [];
        
        return array_merge($this->payloadTaits(), $validate);
    }
}
