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
        $validate = [
            'title'                  => 'required|string',
            'description'            => 'nullable|string',
            'status'                 => 'nullable|string',
            'scheduled_at'           => 'nullable',
            'expires_at'             => 'nullable',
            'publish_schedule'       => 'nullable',
            'expire_schedule'        => 'nullable',
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

        $scheduledAt = $this->scheduled_at ?? $this->publish_schedule;
        $expiresAt = $this->expires_at ?? $this->expire_schedule;

        $merge = [];
        if ($scheduledAt) {
            $merge['scheduled_at'] = $scheduledAt;
            $merge['publish_schedule'] = $scheduledAt;
        }
        if ($expiresAt) {
            $merge['expires_at'] = $expiresAt;
            $merge['expire_schedule'] = $expiresAt;
        }
        if ($this->status) {
            $merge['status'] = $this->status;
            $merge['announcement_status_id'] = $this->announcement_status_id ?? ($this->status === 'Draft' ? 3 : 1);
        }
        
        $this->merge($merge);
    }
}
