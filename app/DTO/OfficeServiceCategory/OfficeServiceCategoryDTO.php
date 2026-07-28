<?php

namespace App\DTO\OfficeServiceCategory;

class OfficeServiceCategoryDTO
{
    public function __construct(
        public int $id,
        public string $type
    ) {}

    public static function fromModel($model): self
    {
        return new self(
            id: $model->id,
            type: $model->type
        );
    }

    public static function fromCollection($collection): array
    {
        return $collection->map(fn($item) => self::fromModel($item))->all();
    }
}
