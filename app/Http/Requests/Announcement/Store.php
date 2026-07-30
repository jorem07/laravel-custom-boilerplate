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
            'title'                  => 'required',
            'description'            => 'required',

            'publish_date'           => 'required|date_format:Y-m-d',
            'publish_time'           => 'required|date_format:H:i,h:i A,h:i a,g:i A,g:i a',
            'publish_schedule'       => 'required|date',
         
            'expire_date'            => 'nullable|date_format:Y-m-d',
            'expire_time'            => 'nullable|date_format:H:i,h:i A,h:i a,g:i A,g:i a',
            'expire_schedule'        => 'nullable|date',

            'created_by'             => 'required|int|exists:users,id,deleted_at,NULL',
            'office_id'              => 'nullable|int|exists:offices,id,deleted_at,NULL',
            'announcement_status_id' => 'required|int|exists:announcement_statuses,id,deleted_at,NULL'
        ];

        return array_merge($this->payloadTaits(), $validate);
    }

    public function prepareForValidation(): void
    {
        $user = $this->user();
        
        $merge = [
            'publish_schedule' => trim(implode(' ', array_filter([
                $this->publish_date,
                $this->publish_time,
            ]))),
            'created_by' => $user->id,
            'office_id'  => $user->office_id ?? null
        ];

        if ($this->expire_date || $this->expire_time) {
            $merge['expire_schedule'] = trim(implode(' ', array_filter([
                $this->expire_date,
                $this->expire_time,
            ])));
        }
        
        $this->merge($merge);
    }
}
