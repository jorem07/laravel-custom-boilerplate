<?php

namespace App\Repositories;

use App\Models\QueueStatus;
use App\Repositories\Contracts\QueueStatusRepositoryInterface;
use App\Traits\QueryGenerator;
use App\Traits\RepositoryTrait;

/**
 * QueueStatusRepository
 *
 * This repository provides a base implementation for QueueStatus data access.
 * You can override or extend this class to customize query logic or add new methods.
 */
class QueueStatusRepository implements QueueStatusRepositoryInterface
{
    use RepositoryTrait;

    // The Category model instance.
    protected QueueStatus $model;
    
    /**
     * Constructor.
     *
     * @param QueueStatus $model The QueueStatus model instance.
     * You can override this constructor in a child class if needed.
     */
    public function __construct(QueueStatus $model)
    {
        $this->model = $model;
    }

    // You can override or add methods here to customize repository
}
