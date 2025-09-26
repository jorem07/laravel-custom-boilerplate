<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;
use Bouncer;

class Index extends FormRequest
{

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {

        return [
            'status'        => 'nullable|boolean',
            'roles'         => 'nullable|array',
            'search'        => 'nullable',
            'skip'          => 'nullable|numeric',
            'take'          => 'nullable|numeric'
        ];
    }
}
