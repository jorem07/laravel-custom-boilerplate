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
        public int $queue_status_id,
        public string $queue_statuses_name
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
            'queue_status_id'    => $this->queue_status_id,
            'queue_statuses_name'=> $this->queue_statuses_name
        ];
    }

    public static function fromModel(Queue $queue): self
    {
        return new self(
            id: $queue->id,
            queue_no: $queue->queue_no,
            office_service_id: $queue->office_service_id,
            counter_id: $queue->counter_id,
            user_id: $queue->user_id,
            uuid: $queue->uuid,
            time_start: $queue->time_start,
            time_end: $queue->time_end,
            queue_status_id: $queue->queue_status_id,
            queue_statuses_name: $queue->queue_status->name
        );
    }

    public static function fromCollection($queues): array
    {
        return $queues
            ->map(fn (Queue $queue) => self::fromModel($queue)->toArray())
            ->toArray();
    }
}