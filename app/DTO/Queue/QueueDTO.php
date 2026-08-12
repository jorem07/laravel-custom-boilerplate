<?php

namespace App\DTO\Queue;

use App\Models\Queue;
use DateTime;

class QueueDTO
{
    public function __construct(public Queue $queue)
    {}

    public function toArray(): array
    {
        $data = $this->queue->toArray();
        $officeService = $this->queue->office_service;
        $queueStatus = $this->queue->queue_status;

        $data['office_id'] = $this->queue->office_id ?? $officeService?->office_id ?? null;
        if (empty($data['office_service_name']) && $officeService) {
            $data['office_service_name'] = $officeService->name;
        }
        if (empty($data['office_service_code']) && $officeService) {
            $data['office_service_code'] = $officeService->code;
        }
        if (empty($data['queue_status_name']) && $queueStatus) {
            $data['queue_status_name'] = $queueStatus->name;
            $data['queue_statuses_name'] = $queueStatus->name;
        }

        $transferredFrom = $this->queue->relationLoaded('transferred_from_counter')
            ? $this->queue->transferred_from_counter
            : null;

        $data['remarks'] = $this->queue->remarks;
        $data['reason'] = $this->queue->remarks;
        $data['transferred_from_counter_id'] = $this->queue->transferred_from_counter_id;
        $data['transferred_from_counter_name'] = $transferredFrom?->name ?? $transferredFrom?->counter_no ?? null;

        return $data;
    }

    public static function fromModel(Queue $queue): self
    {
        if ($queue->relationLoaded('user') && $queue->user) {
            $queue->user->full_name = implode(' ', array_filter([
                $queue->user->first_name,
                $queue->user->middle_name,
                $queue->user->last_name
            ]));
        }

        return new self($queue);
    }

    public static function fromCollection($queues): array
    {
        return $queues
            ->map(fn (Queue $queue) => self::fromModel($queue)->toArray())
            ->toArray();
    }
}