<?php

namespace App\Http\Requests\User;

use App\Traits\PayloadTrait;
use Illuminate\Foundation\Http\FormRequest;
use Bouncer;

class Index extends FormRequest
{
    use PayloadTrait;
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $validate = [];
        
        return array_merge($this->payloadTaits(), $validate);
    }
}
