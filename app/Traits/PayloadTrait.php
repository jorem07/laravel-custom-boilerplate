<?php

namespace App\Traits;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Silber\Bouncer\BouncerFacade;

trait PayloadTrait
{
    private array $validateGlobal;

    /**
     * Determine if the user is authorized to make this request.
     * Override this method to implement custom authorization logic.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return BouncerFacade::can(implode('.', array_filter([
            $this->getTargetTable(),
            class_basename($this)
        ])));
    }
    
    public function payloadTaits() : array
    {
        $this->validateGlobal= [
            'search'         => 'nullable|array',
            'full_search'    => 'nullable',
            'page'           => 'nullable|numeric',
            'show'           => 'nullable|numeric',
            'sort'           => 'nullable|min:1|array',
            'sort.column'    => 'required_with:sort|string',
            'sort.order'     => 'required_with:sort|string|in:asc,desc',
        ];

        self::entityChecker();
        
        return $this->validateGlobal;
    }

    private function entityChecker()
    {
        $class = class_basename($this);
        if ($class !== 'Store'  && $class !== 'Index') {
            $table = $this->getTargetTable();

            $rule = Rule::exists($table)->when(Schema::hasColumn($table, 'deleted_at'), fn($q) => $q->whereNull('deleted_at'));

            $this->validateGlobal['id'] = ['required', $rule];
        }
    }

    private function getTargetTable()
    {
        $namespace = (new \ReflectionClass($this))->getNamespaceName();
        return Str::plural(Str::snake(substr($namespace, strrpos($namespace, '\\') + 1)));
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
                'message'=> $validator->errors()->first(),
                'errors' => $nested
            ], 422)
        );
    }

    public function prepareForValidation() : void
    {
        $this->merge([
            'id'   => $this->route($this->getTargetTable()),
            'sort' => $this->input('sort', [
                'column' => 'updated_at',
                'order'  => 'desc',
            ]),
        ]);
    }
}
