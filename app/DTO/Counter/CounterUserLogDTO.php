<?php

namespace App\DTO\Counter;

use App\Models\CounterUserLog;

class CounterUserLogDTO
{
    public function __construct(
        public int $id,
        public ?int $user_id,
        public ?int $counter_id,
        public ?string $log_in,
        public ?string $log_out,
        public ?string $user_name = null,
        public ?string $counter_name = null,
    ) {}

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'counter_id' => $this->counter_id,
            'log_in' => $this->log_in,
            'log_out' => $this->log_out,
            'user_name' => $this->user_name,
            'counter_name' => $this->counter_name,
        ];
    }

    public static function fromModel(CounterUserLog $log): self
    {
        $userName = null;
        if ($log->relationLoaded('user') && $log->user) {
            $userName = $log->user->fullname;
        }

        return new self(
            id: $log->id,
            user_id: $log->user_id,
            counter_id: $log->counter_id,
            log_in: $log->log_in?->toDateTimeString(),
            log_out: $log->log_out?->toDateTimeString(),
            user_name: $userName ?: null,
            counter_name: $log->relationLoaded('counter') ? $log->counter?->name : null,
        );
    }

    public static function fromCollection($logs): array
    {
        return $logs
            ->map(fn (CounterUserLog $log) => self::fromModel($log)->toArray())
            ->toArray();
    }
}
