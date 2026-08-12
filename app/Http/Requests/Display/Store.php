<?php

namespace App\Http\Requests\Display;

use App\Traits\PayloadTrait;
use Illuminate\Foundation\Http\FormRequest;

class Store extends FormRequest
{
    use PayloadTrait;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return array_merge($this->payloadTaits(), [
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255',
            'location' => 'nullable|string',
            'status' => 'nullable|string',
            'layout_type' => 'nullable|string',
            'connection_status' => 'nullable|string',
            'office_id' => 'nullable|exists:offices,id',
        ]);
    }
}
