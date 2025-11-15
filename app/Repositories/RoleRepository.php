<?php

namespace App\Repositories;

use App\Models\Role;
use App\Traits\QueryGenerator;

/**
 * RoleRepository
 *
 * This repository provides a base implementation for Role data access.
 * You can override or extend this class to customize query logic or add new methods.
 */
class RoleRepository
{
    use QueryGenerator;

    // The Category model instance.
    protected Role $model;
    
    /**
     * Constructor.
     *
     * @param Role $model The Role model instance.
     * You can override this constructor in a child class if needed.
     */
    public function __construct(Role $model)
    {
        $this->model = $model;
    }

    // You can override or add methods here to customize repository
}
