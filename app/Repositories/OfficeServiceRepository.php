<?php

namespace App\Repositories;

use App\Models\OfficeService;
use App\Repositories\Contracts\OfficeServiceRepositoryInterface;
use App\Traits\QueryGenerator;
use App\Traits\RepositoryTrait;

/**
 * OfficeServiceRepository
 *
 * This repository provides a base implementation for OfficeService data access.
 * You can override or extend this class to customize query logic or add new methods.
 */
class OfficeServiceRepository implements OfficeServiceRepositoryInterface
{
    use RepositoryTrait;

    // The Category model instance.
    protected OfficeService $model;
    
    /**
     * Constructor.
     *
     * @param OfficeService $model The OfficeService model instance.
     * You can override this constructor in a child class if needed.
     */
    public function __construct(OfficeService $model)
    {
        $this->model = $model;
    }

    // You can override or add methods here to customize repository
}
