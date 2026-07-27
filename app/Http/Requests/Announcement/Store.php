<?php

namespace App\Http\Requests\Announcement;

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
            'title' => 'required|string',
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
}
