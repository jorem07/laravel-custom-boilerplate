<?php

namespace App\Http\Requests\Announcement;

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
            'id' => ['required', 'exists:announcements,id'],
            'title' => 'sometimes|required|string',
            'status' => 'nullable|string',
            'type' => 'nullable|string',
            'icon' => 'nullable|string',
            'icon_color' => 'nullable|string',
            'icon_bg_color' => 'nullable|string',
            'scheduled_at' => 'nullable|date',
            'expires_at' => 'nullable|date',
            'created_by' => 'nullable|exists:users,id',
        ]);
    }

    public function prepareForValidation(): void
    {
        $this->payloadPrepareForValidation();
        $this->merge(['id' => $this->route('announcements')]);
    }
}
