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
        return array_merge($this->queue->toArray(), []);
    }

    public static function fromModel(Queue $queue): self
    {
        if(isset($queue->user)) $queue->user->full_name = implode(' ', array_filter([
            $queue->user?->first_name,
            $queue->user?->middle_name,
            $queue->user?->last_name
        ]));

        return new self($queue);
    }

    public static function fromCollection($queues): array
    {
        return $queues
            ->map(fn (Queue $queue) => self::fromModel($queue)->toArray())
            ->toArray();
    }
}