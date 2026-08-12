<?php

namespace App\Http\Requests\Display;

use App\Traits\PayloadTrait;
use Illuminate\Foundation\Http\FormRequest;

class Update extends FormRequest
{
    use PayloadTrait {
        PayloadTrait::prepareForValidation as payloadPrepareForValidation;
    }

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return array_merge($this->payloadTaits(), [
            'id' => ['required', 'exists:displays,id'],
            'name' => 'sometimes|required|string|max:255',
            'slug' => 'nullable|string|max:255',
            'location' => 'nullable|string',
            'status' => 'nullable|string',
            'layout_type' => 'nullable|string',
            'connection_status' => 'nullable|string',
            'office_id' => 'nullable|exists:offices,id',
        ]);
    }

    public function prepareForValidation(): void
    {
        $this->payloadPrepareForValidation();
        $this->merge(['id' => $this->route('displays')]);
    }
}
