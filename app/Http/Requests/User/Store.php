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
            'fullname'      => 'required|string',
            'email'         => 'required|email|unique:users,email,NULL,NULL,deleted_at,NULL',
            'phone_number'  => 'nullable|string',
            'role'          => 'required|string',
            'assignment'    => 'nullable|string',
            'status'        => 'required|string',
            'password'      => 'nullable|string',
            'role_id'       => 'nullable|array'
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

        $statusVal = $this->status === 'Active' || $this->status === true || $this->status === 1 || $this->status === '1';

        $this->merge([
            'id'          => $this->route('users'),
            'allow_login' => $statusVal,
        ]);
    }
}
