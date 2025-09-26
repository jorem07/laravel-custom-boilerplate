<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;
use Bouncer;

class Update extends FormRequest
{

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {

        return [
            'id'            => 'required|integer|exists:users,id,deleted_at,NULL',
            "first_name"    =>  "nullable",
            "last_name"     =>  "nullable",
            "middle_name"   =>  "nullable",
            "email"         =>  "nullable|unique:users",
            "allow_login"   =>  "nullable",
            "status"        =>  "nullable",
            "password"      =>  "nullable",
            "role"          =>  "nullable|exists:roles,name"
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'id' => $this->route('users'),
        ]);
    }
}
