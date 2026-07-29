<?php

namespace App\DTO\Queue;

use App\Models\Queue;
use DateTime;

class QueueDTO
{
    public function __construct(
        public int $id,
        public string $queue_no,
        public int $office_service_id,
        public ?int $counter_id,
        public ?int $user_id,
        public string $uuid,
        public ?string $time_start,
        public ?string $time_end,
        public ?int $estimated_wait_minutes = null,
        public ?string $estimated_time_return = null,
        public int $queue_status_id,
        public string $queue_statuses_name,
        public ?string $counter_name = null,
        public ?string $user_name = null,
    ) {}

    public function toArray(): array
    {
        return [
            'id'                 => $this->id,
            'queue_no'           => $this->queue_no,
            'office_service_id'  => $this->office_service_id,
            'counter_id'         => $this->counter_id,
            'user_id'            => $this->user_id,
            'uuid'               => $this->uuid,
            'time_start'         => $this->time_start,
            'time_end'           => $this->time_end,
            'estimated_wait_minutes' => $this->estimated_wait_minutes,
            'estimated_time_return'  => $this->estimated_time_return,
            'queue_status_id'    => $this->queue_status_id,
            'queue_statuses_name'=> $this->queue_statuses_name,
            'counter_name'       => $this->counter_name,
            'user_name'          => $this->user_name,
        ];
    }

    public static function fromModel(Queue $queue): self
    {
        $userName = null;
        if ($queue->relationLoaded('user') && $queue->user) {
            $userName = trim("{$queue->user->first_name} {$queue->user->last_name}");
        }

        return new self(
            id: $queue->id,
            queue_no: $queue->queue_no,
            office_service_id: $queue->office_service_id,
            counter_id: $queue->counter_id,
            user_id: $queue->user_id,
            uuid: $queue->uuid,
            time_start: $queue->time_start instanceof \DateTimeInterface
                ? $queue->time_start->format('Y-m-d H:i:s')
                : $queue->time_start,
            time_end: $queue->time_end instanceof \DateTimeInterface
                ? $queue->time_end->format('Y-m-d H:i:s')
                : $queue->time_end,
            estimated_wait_minutes: $queue->estimated_wait_minutes,
            estimated_time_return: $queue->estimated_time_return?->toDateTimeString(),
            queue_status_id: $queue->queue_status_id,
            queue_statuses_name: $queue->queue_status->name,
            counter_name: $queue->relationLoaded('counter') ? $queue->counter?->name : null,
            user_name: $userName ?: null,
        );
    }

    public static function fromCollection($queues): array
    {
        return $queues
            ->map(fn (Queue $queue) => self::fromModel($queue)->toArray())
            ->toArray();
    }
}