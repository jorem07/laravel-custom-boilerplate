<?php

namespace App\Http\Requests\OfficeService;

use Illuminate\Foundation\Http\FormRequest;
use Bouncer;
use App\Traits\PayloadTrait;

use Illuminate\Validation\Rule;

/**
 * Update
 *
 * This request class handles validation and authorization for the request.
 * You can override the authorize() and rules() methods as needed.
 */
class Update extends FormRequest
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
        $validate = [
            'name'      => ['required', Rule::unique('office_services', 'name')->ignore($this->id)->whereNull('deleted_at')],
            'office_id' => ['nullable', Rule::exists('offices', 'id')->whereNull('deleted_at')],
            'code'      => ['nullable', 'string', Rule::unique('office_services', 'code')->ignore($this->id)],
            'letter'    => ['nullable', 'string'],
            'avg_time'  => ['nullable', 'string'],
            'priority'  => ['nullable', 'string'],
            'status'    => ['nullable', 'string'],
            'color'     => ['nullable', 'string'],
            'requirements'   => ['nullable', 'array'],
            'requirements.*' => ['nullable', 'string'],
        ];

        $class = class_basename($this);
        if ($class !== 'Store'  && $class !== 'Index') {
            $validate['id'] = ['required', 'exists:office_services,id'];
        }
        
        return array_merge($this->payloadTaits(), $validate);
    }

    public function prepareForValidation(): void
    {
        $this->payloadPrepareForValidation();

        $this->merge([
            'id'    => $this->route('office_services')
        ]);
    }
}
