<?php

namespace App\Http\Requests\Role;

use Illuminate\Foundation\Http\FormRequest;
use Bouncer;

class Show extends FormRequest
{

    public function rules(): array
    {
        return [
            'id' => 'required|integer|exists:roles,id'
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'id' => $this->route('roles'),
        ]);
    }
}
