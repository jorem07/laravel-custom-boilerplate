<?php

namespace App\Repositories;

use App\Models\Queue;
use App\Repositories\Contracts\QueueRepositoryInterface;
use App\Traits\QueryGenerator;
use App\Traits\RepositoryTrait;

/**
 * QueueRepository
 *
 * This repository provides a base implementation for Queue data access.
 * You can override or extend this class to customize query logic or add new methods.
 */
class QueueRepository implements QueueRepositoryInterface
{
    use RepositoryTrait;

    // The Category model instance.
    protected Queue $model;
    
    /**
     * Constructor.
     *
     * @param Queue $model The Queue model instance.
     * You can override this constructor in a child class if needed.
     */
    public function __construct(Queue $model)
    {
        $this->model = $model;
    }

    // You can override or add methods here to customize repository
}
