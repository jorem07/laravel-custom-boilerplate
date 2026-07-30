<?php

namespace App\Repositories;

use App\Models\AnnouncementStatus;
use App\Repositories\Contracts\AnnouncementStatusRepositoryInterface;
use App\Traits\RepositoryTrait;

/**
 * AnnouncementStatusRepository
 *
 * This repository provides a base implementation for AnnouncementStatus data access.
 * You can override or extend this class to customize query logic or add new methods.
 */
class AnnouncementStatusRepository implements AnnouncementStatusRepositoryInterface
{
    use RepositoryTrait;

    // The Category model instance.
    protected AnnouncementStatus $model;
    
    /**
     * Constructor.
     *
     * @param AnnouncementStatus $model The AnnouncementStatus model instance.
     * You can override this constructor in a child class if needed.
     */
    public function __construct(AnnouncementStatus $model)
    {
        $this->model = $model;
    }

    // You can override or add methods here to customize repository
}
