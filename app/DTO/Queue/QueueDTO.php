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
        public string $queue_statuses_name,
        public ?string $queue_status_name = null,
        public ?string $counter_name = null,
        public ?string $user_name = null,
        public ?string $client_name = null,
        public ?string $client_phone = null,
        public ?string $office_service_name = null,
        public ?string $created_at = null,
    ) {}

    public function toArray(): array
    {
        return [
            'id'                  => $this->id,
            'queue_no'            => $this->queue_no,
            'office_service_id'   => $this->office_service_id,
            'counter_id'          => $this->counter_id,
            'user_id'             => $this->user_id,
            'uuid'                => $this->uuid,
            'time_start'          => $this->time_start,
            'time_end'            => $this->time_end,
            'queue_status_id'     => $this->queue_status_id,
            'queue_statuses_name' => $this->queue_statuses_name,
            'queue_status_name'   => $this->queue_status_name ?? $this->queue_statuses_name,
            'counter_name'        => $this->counter_name,
            'user_name'           => $this->user_name,
            'client_name'         => $this->client_name,
            'client_phone'        => $this->client_phone,
            'office_service_name' => $this->office_service_name,
            'created_at'          => $this->created_at,
        ];
    }

    public static function fromModel(Queue $queue): self
    {
        $userName = null;
        if ($queue->relationLoaded('user') && $queue->user) {
            $userName = $queue->user->fullname;
        }

        $serviceName = null;
        if ($queue->relationLoaded('office_service') && $queue->office_service) {
            $serviceName = $queue->office_service->name;
        }

        $statusName = $queue->relationLoaded('queue_status') && $queue->queue_status ? $queue->queue_status->name : 'Waiting';

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
            queue_statuses_name: $statusName,
            queue_status_name: $statusName,
            counter_name: $queue->relationLoaded('counter') ? $queue->counter?->name : null,
            user_name: $userName ?: null,
            client_name: $queue->client_name ?: null,
            client_phone: $queue->client_phone ?: null,
            office_service_name: $serviceName ?: null,
            created_at: $queue->created_at ? (string) $queue->created_at : null,
        );
    }

    public static function fromCollection($queues): array
    {
        return $queues
            ->map(fn (Queue $queue) => self::fromModel($queue)->toArray())
            ->toArray();
    }
}