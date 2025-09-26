<?php

namespace App\Http\Requests\Role;

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
            'search'        => 'nullable',
            'skip'          => 'nullable|numeric',
            'take'          => 'nullable|numeric'
        ];
    }
}
