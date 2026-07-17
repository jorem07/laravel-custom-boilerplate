<?php
namespace App\DTO\QueueStatus;

use App\Models\QueueStatus;

class QueueStatusDTO
{
    

    public function __construct(
        public int $id,
        public string $name
    )
    {}

    public static function fromModel(QueueStatus $queueStatus) : self
    {
        return new self(
            id: $queueStatus->id,
            name: $queueStatus->name
        );
    }

    public static function fromCollection($queueStatuss): array
    {
        return $queueStatuss
            ->map(fn (QueueStatus $queueStatus) => self::fromModel($queueStatus))
            ->toArray();
    }
}
