<?php

namespace App\Http\Requests\Announcement;

use App\Traits\PayloadTrait;
use Illuminate\Foundation\Http\FormRequest;

class Delete extends FormRequest
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
            'id' => ['required', 'exists:announcements,id'],
        ]);
    }

    public function prepareForValidation(): void
    {
        $this->payloadPrepareForValidation();
        $this->merge(['id' => $this->route('announcements')]);
    }
}
