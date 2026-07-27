<?php

namespace App\Http\Requests\Display;

use App\Traits\PayloadTrait;
use Illuminate\Foundation\Http\FormRequest;

class Index extends FormRequest
{
    use PayloadTrait;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return array_merge($this->payloadTaits(), []);
    }
}
