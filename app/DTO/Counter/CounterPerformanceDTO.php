<?php

namespace App\DTO\Counter;

use App\Models\CounterPerformance;

class CounterPerformanceDTO
{
    public function __construct(
        public int $counter_id,
        public string $counter_name,
        public int $served,
        public float $avg_time_minutes,
        public float $efficiency_percent,
    ) {}

    public function toArray(): array
    {
        return [
            'counter_id' => $this->counter_id,
            'counter_name' => $this->counter_name,
            'served' => $this->served,
            'avg_time_minutes' => $this->avg_time_minutes,
            'avg_time_label' => round($this->avg_time_minutes) . ' mins',
            'efficiency_percent' => $this->efficiency_percent,
        ];
    }

    public static function fromModel(CounterPerformance $performance): self
    {
        return new self(
            counter_id: $performance->counter_id,
            counter_name: $performance->relationLoaded('counter')
                ? ($performance->counter?->name ?? 'Counter ' . $performance->counter_id)
                : 'Counter ' . $performance->counter_id,
            served: (int) $performance->served_count,
            avg_time_minutes: (float) ($performance->avg_time_minutes ?? 0),
            efficiency_percent: (float) ($performance->efficiency_percent ?? 0),
        );
    }

    public static function fromCollection($performances): array
    {
        return $performances
            ->map(fn (CounterPerformance $performance) => self::fromModel($performance)->toArray())
            ->toArray();
    }
}
