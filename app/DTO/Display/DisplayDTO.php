<?php

namespace App\DTO\Display;

use App\Models\Display;

class DisplayDTO
{
    public function __construct(
        public int $id,
        public string $name,
        public ?string $location = null,
        public string $status = 'Active',
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
            'location' => $this->location,
            'status' => $this->status,
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
            location: $model->location,
            status: $model->status,
            connection_status: $model->connection_status,
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
