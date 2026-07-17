<?php

namespace App\Http\Requests\Queue;

use Illuminate\Foundation\Http\FormRequest;
use Bouncer;
use App\Traits\PayloadTrait;
use Carbon\Carbon;
use Illuminate\Support\Str;

/**
 * Store
 *
 * This request class handles validation and authorization for the request.
 * You can override the authorize() and rules() methods as needed.
 */
class Store extends FormRequest
{
    use PayloadTrait {
        PayloadTrait::prepareForValidation as payloadPrepareForValidation;
    }

    /**
     * Determine if the user is authorized to make this request.
     * Override this method to implement custom authorization logic.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     * Override this method to define custom validation rules.
     *
     * @return array
     */
    public function rules(): array
    {
        // Add your validation rules here
        $validate = [
            'queue_status_id'   => 'required|exists:queue_statuses,id,deleted_at,NULL',
            'office_service_id' => 'required|exists:office_services,id,deleted_at,NULL',
            'uuid'              => 'required|uuid|unique:queues,uuid',
            'time_start'        => 'required'
        ];

        $class = class_basename($this);
        if ($class !== 'Store'  && $class !== 'Index') {
            $validate['id'] = ['required', 'exists:queues,id'];
        }
        
        return array_merge($this->payloadTaits(), $validate);
    }

    public function prepareForValidation(): void
    {
        $this->payloadPrepareForValidation();
        $uuid = Str::uuid7();

        $this->merge([
            'id'            => $this->route('queues'),
            'uuid'          => (string) $uuid,
            'time_start'    => Carbon::now()
        ]);
    }
}
