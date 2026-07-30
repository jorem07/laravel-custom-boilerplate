<?php

namespace App\DTO\Announcement;

use App\Models\Announcement;

class AnnouncementDTO
{
    public function __construct(public Announcement $announcement) {}

    public function toArray(): array
    {
        return array_merge($this->announcement->toArray(), []);
    }

    public static function fromModel(Announcement $announcement): self
    {
        $announcement->created_by_full_name = isset($announcement->created_by_user) ? implode(' ', array_filter([
            $announcement->created_by_user->first_name,
            $announcement->created_by_user->middle_name ? str_split($announcement->created_by_user->middle_name, 1)[0] . '.' : null,
            $announcement->created_by_user->last_name
        ])) : null;

        return new self($announcement);
    }

    public static function fromCollection($announcements): array
    {
        return $announcements
            ->map(fn (Announcement $model) => self::fromModel($model)->toArray())
            ->toArray();
    }
}
