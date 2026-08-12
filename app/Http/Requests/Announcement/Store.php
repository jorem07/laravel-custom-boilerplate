<?php

namespace App\Http\Requests\Announcement;

use App\Traits\PayloadTrait;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class Store extends FormRequest
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
        $validate = [
            'title'                  => 'required|string',
            'description'            => 'nullable|string',
            'status'                 => 'nullable|string',
            'scheduled_at'           => 'nullable',
            'expires_at'             => 'nullable',
            'publish_schedule'       => 'nullable',
            'expire_schedule'        => 'nullable',
            'created_by'             => 'nullable|int|exists:users,id,deleted_at,NULL',
            'office_id'              => 'nullable|int|exists:offices,id,deleted_at,NULL',
            'announcement_status_id' => 'nullable|int|exists:announcement_statuses,id,deleted_at,NULL',
            'type'                   => 'nullable|string',
            'icon'                   => 'nullable|string',
            'icon_color'             => 'nullable|string',
            'icon_bg_color'          => 'nullable|string',
        ];

        return array_merge($this->payloadTaits(), $validate);
    }

    public function prepareForValidation(): void
    {
        $this->payloadPrepareForValidation();

        $user = $this->user();
        $statusVal = $this->status ?? 'Published';
        $statusId = $this->announcement_status_id ?? ($statusVal === 'Draft' ? 3 : 1);

        $scheduledAt = $this->scheduled_at ?? $this->publish_schedule;
        $expiresAt = $this->expires_at ?? $this->expire_schedule;

        $merge = [
            'status'                 => $statusVal,
            'announcement_status_id' => $statusId,
            'description'            => $this->description ?? $this->title,
            'scheduled_at'           => $scheduledAt,
            'expires_at'             => $expiresAt,
            'publish_schedule'       => $scheduledAt,
            'expire_schedule'        => $expiresAt,
            'created_by'             => $user?->id ?? $this->created_by ?? 1,
            'office_id'              => $this->office_id ?? null,
        ];

        $this->merge($merge);
    }
}
