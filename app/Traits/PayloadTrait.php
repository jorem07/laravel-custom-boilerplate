<?php

namespace App\Traits;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Arr;

trait PayloadTrait
{
    public function payloadTaits() : array
    {
        return [
            'search'         => 'nullable|array',
            'full_search'    => 'nullable',
            'page'           => 'nullable|numeric',
            'show'           => 'nullable|numeric',
            'sort'           => 'nullable|min:1|array',
            'sort.column'    => 'required_with:sort|string',
            'sort.order'     => 'required_with:sort|string|in:asc,desc',
        ];
    }

    /**
    * Handle a failed validation attempt.
    * 
    * @param \Illuminate\Contracts\Validation\Validator $validator
    * @throws \Illuminate\Http\Exceptions\HttpResponseException
    */
    public function failedValidation(Validator $validator)
    {
        $errors = $validator->errors()->toArray();

        $nested = Arr::undot($errors);

        throw new HttpResponseException(
            response()->json([
                'errors' => $nested
            ], 422)
        );
    }

    public function prepareForValidation() : void
    {
        $this->merge([
            'sort' => $this->input('sort', [
                'column' => 'updated_at',
                'order'  => 'desc',
            ]),
        ]);
    }
}
