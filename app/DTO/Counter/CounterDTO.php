<?php

namespace App\DTO\Counter;

use App\Models\Counter;

class CounterDTO
{
    public function __construct(
        public int $id,
        public string $name,
        public ?int $user_id,
        public ?int $office_service_id,
        public ?string $user_name = null,
        public ?string $office_service_name = null,
        public bool $is_logged_in = false,
    ) {}

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'user_id' => $this->user_id,
            'office_service_id' => $this->office_service_id,
            'user_name' => $this->user_name,
            'office_service_name' => $this->office_service_name,
            'is_logged_in' => $this->is_logged_in,
        ];
    }

    public static function fromModel(Counter $counter): self
    {
        $userName = null;
        if ($counter->relationLoaded('user') && $counter->user) {
            $userName = $counter->user->fullname;
        }

        return new self(
            id: $counter->id,
            name: $counter->name,
            user_id: $counter->user_id,
            office_service_id: $counter->office_service_id,
            user_name: $userName ?: null,
            office_service_name: $counter->relationLoaded('office_service')
                ? $counter->office_service?->name
                : null,
            is_logged_in: !is_null($counter->user_id),
        );
    }

    public static function fromCollection($counters): array
    {
        return $counters
            ->map(fn (Counter $counter) => self::fromModel($counter)->toArray())
            ->toArray();
    }
}
