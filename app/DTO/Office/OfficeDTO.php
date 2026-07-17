<?php

namespace App\DTO\Office;

class OfficeDTO
{
    public function __construct(
        public int $id,
        public string $name
    ) {}

    public static function fromModel($model): self
    {
        return new self(
            id: $model->id,
            name: $model->name
        );
    }

    public static function fromCollection($collection): array
    {
        return $collection->map(fn($item) => self::fromModel($item))->all();
    }
}
