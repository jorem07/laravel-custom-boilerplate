<?php

namespace App\Http\Requests\User;

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
            'first_name'    => 'required|string',
            'middle_name'   => 'required|string',
            'last_name'     => 'required|string',
            'allow_login'   => 'required|boolean',
            'status'        => 'required|boolean',
            'password'      => 'required|string|confirmed',
            'email'         => 'required|unique:users,email,NULL,NULL,deleted_at,NULL',
            'role_id'       => 'required|array|exists:roles,id,deleted_at,NULL'
        ];

        $class = class_basename($this);
        if ($class !== 'Store'  && $class !== 'Index') {
            $validate['id'] = ['required', 'exists:users,id'];
        }
        
        return array_merge($this->payloadTaits(), $validate);
    }

    public function prepareForValidation(): void
    {
        $this->payloadPrepareForValidation();

        $this->merge([
            'id'    => $this->route('users')
        ]);
    }
}
