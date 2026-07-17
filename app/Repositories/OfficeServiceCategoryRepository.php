<?php

namespace App\Repositories;

use App\Models\OfficeServiceCategory;
use App\Repositories\Contracts\OfficeServiceCategoryRepositoryInterface;
use App\Traits\QueryGenerator;
use App\Traits\RepositoryTrait;

/**
 * OfficeServiceCategoryRepository
 *
 * This repository provides a base implementation for OfficeServiceCategory data access.
 * You can override or extend this class to customize query logic or add new methods.
 */
class OfficeServiceCategoryRepository implements OfficeServiceCategoryRepositoryInterface
{
    use RepositoryTrait;

    // The Category model instance.
    protected OfficeServiceCategory $model;
    
    /**
     * Constructor.
     *
     * @param OfficeServiceCategory $model The OfficeServiceCategory model instance.
     * You can override this constructor in a child class if needed.
     */
    public function __construct(OfficeServiceCategory $model)
    {
        $this->model = $model;
    }

    // You can override or add methods here to customize repository
}
