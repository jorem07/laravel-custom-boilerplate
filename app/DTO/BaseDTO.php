<?php

namespace App\DTO;

use Illuminate\Database\Eloquent\Model;

class BaseDTO
{
    public function __construct(public Model $model)
    {}

    public function toArray(): array
    {
        return array_merge($this->model->toArray(), []);
    }

    public static function fromModel(Model $model) : self
    {
        return new self($model);
    }

    public static function fromCollection($models): array
    {
        return $models
            ->map(fn (Model $model) => self::fromModel($model)->toArray())
            ->toArray();
    }
}