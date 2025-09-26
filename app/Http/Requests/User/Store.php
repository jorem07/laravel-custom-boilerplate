<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;
use Bouncer;

class Store extends FormRequest
{

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {

        return [
            
            "first_name"    =>  "required",
            "last_name"     =>  "required",
            "middle_name"   =>  "nullable",
            "email"         =>  "required|unique:users",
            "allow_login"   =>  "required",
            "status"        =>  "required",
            "password"      =>  "required",
            "role"          =>  "required|exists:roles,name"
        ];
    }
}
