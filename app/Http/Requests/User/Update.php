<?php

namespace App\Http\Requests\User;

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
            'fullname'        => 'required|string',
            'email'           => 'required|email|unique:users,email,' . $this->id . ',id,deleted_at,NULL',
            'phone_number'    => 'nullable|string',
            'role'            => 'sometimes|required|string',
            'assignment'      => 'nullable|string',
            'status'          => 'required|string',
            'current_password'=> $this->is_admin ? 'nullable' : 'required|current_password',
            'new_password'    => 'nullable|string',
            'password'        => 'nullable|string',
            'role_id'         => 'nullable|array',
            'is_admin'        => 'required|boolean'
        ];

        $class = class_basename($this);
        if ($class !== 'Store'  && $class !== 'Index') {
            $validate['id'] = ['required', 'exists:users,id,deleted_at,NULL'];
        }
        
        return array_merge($this->payloadTaits(), $validate);
    }

    public function prepareForValidation(): void
    {
        $this->payloadPrepareForValidation();

        $user = $this->user()?->load(['roles']);
        $roles = $user ? $user->roles->pluck('name')->toArray() : [];

        $statusVal = $this->status === 'Active' || $this->status === true || $this->status === 1 || $this->status === '1';

        $this->merge([
            'id'          => $this->route('users'),
            'allow_login' => $statusVal,
            'is_admin'    => (bool) array_intersect(['admin', 'super-admin'], $roles)
        ]);
    }
}
