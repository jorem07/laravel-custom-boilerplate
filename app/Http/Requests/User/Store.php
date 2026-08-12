<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;
use App\Traits\PayloadTrait;
use Illuminate\Validation\Rule;
use Silber\Bouncer\BouncerFacade as Bouncer;

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
        $validate = [
            'first_name'    => 'required|string',
            'middle_name'   => 'nullable|string',
            'last_name'     => 'required|string',
            'allow_login'   => 'required|boolean',
            'status'        => 'required|boolean',
            'password'      => 'required|string',
            'email'         => ['required', Rule::unique('users')->whereNull('deleted_at')],
            'role_id'       => 'required|array',
            'role_id.*'     => 'exists:roles,id,deleted_at,NULL',
            'office_id'     => 'nullable|exists:offices,id,deleted_at,NULL',
            'office_ids'    => 'nullable|array',
            'office_ids.*'  => 'exists:offices,id,deleted_at,NULL',
        ];
        
        return array_merge($this->payloadTaits(), $validate);
    }
}
