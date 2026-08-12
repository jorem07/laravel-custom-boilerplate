<?php

namespace App\Http\Requests\OfficeService;

use Illuminate\Foundation\Http\FormRequest;
use Bouncer;
use App\Traits\PayloadTrait;
use Illuminate\Validation\Rule;

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
     * Get the validation rules that apply to the request.
     * Override this method to define custom validation rules.
     *
     * @return array
     */
    public function prepareForValidation(): void
    {
        $this->payloadPrepareForValidation();

        $rawReqs = $this->requirements ?? $this->requirement ?? [];
        if (!is_array($rawReqs)) {
            $rawReqs = [$rawReqs];
        }

        $normalized = collect($rawReqs)->map(function ($item) {
            $text = is_array($item)
                ? ($item['list'] ?? $item['name'] ?? $item['text'] ?? '')
                : (string) $item;
            $text = trim((string) $text);
            if (!$text) return null;

            $rawId = is_array($item) ? ($item['id'] ?? null) : null;
            return [
                'id' => $rawId ? (int) $rawId : null,
                'list' => $text,
            ];
        })->filter()->values()->toArray();

        if (empty($normalized)) {
            $normalized = [
                ['id' => null, 'list' => 'General Requirements']
            ];
        }

        $this->merge(['requirements' => $normalized]);
    }

    public function rules(): array
    {
        $validate = [
            'name'                       => ['required', 'string', Rule::unique('office_services')->where(fn ($q) => $q->where('office_id', $this->office_id))->whereNull('deleted_at')],
            'code'                       => 'nullable|string|max:10',
            'office_id'                  => 'required|exists:offices,id,deleted_at,NULL',
            'office_service_category_id' => 'required|exists:office_service_categories,id,deleted_at,NULL',
            'subtitle'                   => 'nullable|string',
            'icon'                       => 'nullable|string',
            'icon_color'                 => 'nullable|string',
            'requirements'               => ['required', 'array', 'min:1'],
            'requirements.*.id'          => 'nullable|numeric',
            'requirements.*.list'        => 'required|string|max:255',
        ];

        return array_merge($this->payloadTaits(), $validate);
    }
}
