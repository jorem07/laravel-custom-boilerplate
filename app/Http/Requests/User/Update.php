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
     * Get the validation rules that apply to the request.
     * Override this method to define custom validation rules.
     *
     * @return array
     */
    public function rules(): array
    {
        $validate = [
            'first_name'      => 'required|string',
            'middle_name'     => 'nullable|string',
            'last_name'       => 'required|string',
            'allow_login'     => 'required|boolean',
            'status'          => 'required|boolean',
            'current_password'=> 'nullable',
            'new_password'    => 'nullable|string',
            'password'        => 'nullable|string',
            'email'           => ['required', Rule::unique('users')->ignore($this->id)->whereNull('deleted_at')],
            'role_id'         => 'required|array',
            'role_id.*'       => 'exists:roles,id,deleted_at,NULL',
            'office_id'       => 'nullable|exists:offices,id,deleted_at,NULL',
            'office_ids'      => 'nullable|array',
            'office_ids.*'    => 'exists:offices,id,deleted_at,NULL',
            'is_admin'        => 'nullable|boolean'
        ];
        
        return array_merge($this->payloadTaits(), $validate);
    }

    public function prepareForValidation(): void
    {
        $this->payloadPrepareForValidation();

        $user = $this->user()?->load(['roles']);
        $roles = $user ? $user->roles->pluck('name')->toArray() : [];

        $merge = [
            'is_admin' => (bool) array_intersect(['admin', 'super-admin'], $roles)
        ];

        if ($this->new_password) {
            $merge['password'] = $this->new_password;
        }

        $this->merge($merge);
    }
}
