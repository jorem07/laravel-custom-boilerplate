<?php

namespace App\Http\Requests\Role;

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
            'id'         => 'required|integer|exists:roles,id',
            'ability_id' => 'nullable|array|exists:abilities,id',
            'name'       => 'nullable|unique:roles,name',
            'title'      => 'required_with:name|unique:roles,title',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'id' => $this->route('roles'),
        ]);
    }
}
