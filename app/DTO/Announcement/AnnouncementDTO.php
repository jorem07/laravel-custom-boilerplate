<?php

namespace App\DTO\Announcement;

use App\Models\Announcement;

class AnnouncementDTO
{
    public function __construct(
        public int $id,
        public string $title,
        public string $status,
        public ?string $type = null,
        public ?string $icon = null,
        public ?string $icon_color = null,
        public ?string $icon_bg_color = null,
        public ?string $scheduled_at = null,
        public ?string $expires_at = null,
        public ?int $created_by = null,
        public ?string $created_by_name = null,
        public ?string $created_at = null,
    ) {}

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'status' => $this->status,
            'type' => $this->type,
            'icon' => $this->icon,
            'icon_color' => $this->icon_color,
            'icon_bg_color' => $this->icon_bg_color,
            'scheduled_at' => $this->scheduled_at,
            'expires_at' => $this->expires_at,
            'created_by' => $this->created_by,
            'created_by_name' => $this->created_by_name,
            'created_at' => $this->created_at,
        ];
    }

    public static function fromModel(Announcement $model): self
    {
        $createdByName = null;
        if ($model->relationLoaded('created_by_user') && $model->created_by_user) {
            $createdByName = $model->created_by_user->fullname;
        }

        return new self(
            id: $model->id,
            title: $model->title,
            status: $model->status,
            type: $model->type,
            icon: $model->icon,
            icon_color: $model->icon_color,
            icon_bg_color: $model->icon_bg_color,
            scheduled_at: $model->scheduled_at?->toDateTimeString(),
            expires_at: $model->expires_at?->toDateTimeString(),
            created_by: $model->created_by,
            created_by_name: $createdByName,
            created_at: $model->created_at?->toDateTimeString(),
        );
    }

    public static function fromCollection($announcements): array
    {
        return $announcements
            ->map(fn (Announcement $model) => self::fromModel($model)->toArray())
            ->toArray();
    }
}
