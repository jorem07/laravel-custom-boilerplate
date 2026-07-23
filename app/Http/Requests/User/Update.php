<?php

namespace App\Http\Requests\User;

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
            'first_name'      => 'required|string',
            'middle_name'     => 'required|string',
            'last_name'       => 'required|string',
            'allow_login'     => 'required|boolean',
            'status'          => 'required|boolean',
            'current_password'=> $this->is_admin ? 'nullable' : 'required|current_password',
            'new_password'    => 'nullable|string',
            'password'        => 'nullable|string|confirmed',
            'email'           => ['required', Rule::unique('users')->ignore($this->id)->whereNull('deleted_at')],
            'role_id'         => 'required|array|exists:roles,id,deleted_at,NULL',
            'is_admin'        => 'required|boolean'
        ];
        
        return array_merge($this->payloadTaits(), $validate);
    }

    public function prepareForValidation(): void
    {
        $this->payloadPrepareForValidation();

        $user = $this->user()->load(['roles']);

        $roles = $user->roles->pluck('name')->toArray();

        $this->merge([
            'password'  => $this->new_password,
            'is_admin'  => (bool) array_intersect(['admin', 'super-admin'], $roles)
        ]);
    }
}
