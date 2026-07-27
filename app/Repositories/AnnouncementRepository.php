<?php

namespace App\Repositories;

use App\Models\Announcement;
use App\Repositories\Contracts\AnnouncementRepositoryInterface;
use App\Traits\RepositoryTrait;

class AnnouncementRepository implements AnnouncementRepositoryInterface
{
    use RepositoryTrait;

    protected Announcement $model;

    public function __construct(Announcement $model)
    {
        $this->model = $model;
    }
}
