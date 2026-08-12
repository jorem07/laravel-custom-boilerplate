<?php

namespace App\DTO\Display;

use App\Models\Display;

class DisplayDTO
{
    public function __construct(
        public int $id,
        public string $name,
        public ?string $slug = null,
        public ?string $location = null,
        public string $status = 'Active',
        public string $layout_type = 'standard',
        public string $connection_status = 'Disconnected',
        public ?string $last_heartbeat_at = null,
        public ?int $office_id = null,
        public ?string $office_name = null,
    ) {}

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'location' => $this->location,
            'status' => $this->status,
            'layout_type' => $this->layout_type,
            'connection_status' => $this->connection_status,
            'last_heartbeat_at' => $this->last_heartbeat_at,
            'office_id' => $this->office_id,
            'office_name' => $this->office_name,
        ];
    }

    public static function fromModel(Display $model): self
    {
        $officeName = null;
        if ($model->relationLoaded('office') && $model->office) {
            $officeName = $model->office->name;
        }

        return new self(
            id: $model->id,
            name: $model->name,
            slug: $model->slug,
            location: $model->location,
            status: $model->status ?? 'Active',
            layout_type: $model->layout_type ?? 'standard',
            connection_status: $model->connection_status ?? 'Disconnected',
            last_heartbeat_at: $model->last_heartbeat_at?->toDateTimeString(),
            office_id: $model->office_id,
            office_name: $officeName,
        );
    }

    public static function fromCollection($displays): array
    {
        return $displays
            ->map(fn (Display $model) => self::fromModel($model)->toArray())
            ->toArray();
    }
}
