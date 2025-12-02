<?php

namespace App\Repositories;

use App\Models\User;
use App\Traits\QueryGenerator;

/**
 * UserRepository
 *
 * This repository provides a base implementation for User data access.
 * You can override or extend this class to customize query logic or add new methods.
 */
class UserRepository
{
    use QueryGenerator;

    // The Category model instance.
    protected User $model;
    
    /**
     * Constructor.
     *
     * @param User $model The User model instance.
     * You can override this constructor in a child class if needed.
     */
    public function __construct(User $model)
    {
        $this->model = $model;
    }

    // You can override or add methods here to customize repository
}
