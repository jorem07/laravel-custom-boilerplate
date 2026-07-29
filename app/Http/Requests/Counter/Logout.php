<?php

namespace App\Http\Requests\Counter;

use App\Traits\PayloadTrait;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\DB;

class Logout extends FormRequest
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
        return [
            'counter_id'=> 'required|exists:counters,id,deleted_at,NULL',
            'has_user'  => 'accepted'
        ];
    }

    public function messages()
    {
        return [
            'has_user.accepted' => 'No user is currently logged in to this counter.'
        ];
    }

    public function prepareForValidation(): void
    {
        $this->merge([
            'has_user'  => (bool) DB::table('counters')->find($this->counter_id)->user_id
        ]);
    }
}
