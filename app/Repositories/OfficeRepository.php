<?php

namespace App\Repositories;

use App\Models\Office;
use App\Repositories\Contracts\OfficeRepositoryInterface;
use App\Traits\QueryGenerator;
use App\Traits\RepositoryTrait;

/**
 * OfficeRepository
 *
 * This repository provides a base implementation for Office data access.
 * You can override or extend this class to customize query logic or add new methods.
 */
class OfficeRepository implements OfficeRepositoryInterface
{
    use RepositoryTrait;

    // The Category model instance.
    protected Office $model;
    
    /**
     * Constructor.
     *
     * @param Office $model The Office model instance.
     * You can override this constructor in a child class if needed.
     */
    public function __construct(Office $model)
    {
        $this->model = $model;
    }

    // You can override or add methods here to customize repository
}
