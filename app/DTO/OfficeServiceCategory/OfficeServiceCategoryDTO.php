<?php

namespace App\DTO\OfficeServiceCategory;

class OfficeServiceCategoryDTO
{
    public function __construct(
        public int $id,
        public string $type,
        public ?int $office_id = null
    ) {}

    public static function fromModel($model): self
    {
        $officeId = $model->office_id ?? ($model->relationLoaded('office') ? $model->office?->id : null);
        return new self(
            id: (int) $model->id,
            type: $model->type,
            office_id: $officeId ? (int) $officeId : null
        );
    }

    public static function fromCollection($collection): array
    {
        return $collection->map(fn($item) => self::fromModel($item)->toArray())->all();
    }

    public function toArray(): array
    {
        return [
            'id'        => $this->id,
            'type'      => $this->type,
            'office_id' => $this->office_id,
        ];
    }
}
