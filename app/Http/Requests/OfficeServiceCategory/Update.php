<?php

namespace App\Http\Requests\OfficeServiceCategory;

use Illuminate\Foundation\Http\FormRequest;
use Bouncer;
use App\Traits\PayloadTrait;
use Illuminate\Validation\Rule;

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
    public function prepareForValidation(): void
    {
        $this->payloadPrepareForValidation();
        $targetId = $this->id ?? $this->route('office_service_categories') ?? $this->route('id');
        if ($targetId) {
            $this->merge(['id' => (int) $targetId]);
        }
    }

    public function rules(): array
    {
        $targetId = $this->id ?? $this->route('office_service_categories') ?? $this->route('id');
        $validate = [
            'type'      => ['required', 'string', 'max:255', Rule::unique('office_service_categories', 'type')->ignore($targetId)->whereNull('deleted_at')],
            'office_id' => 'nullable|exists:offices,id,deleted_at,NULL',
        ];

        return array_merge($this->payloadTaits(), $validate);
    }
}
