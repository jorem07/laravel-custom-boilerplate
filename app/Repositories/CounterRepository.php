<?php

namespace App\Repositories;

use App\Models\Counter;
use App\Repositories\Contracts\CounterRepositoryInterface;
use App\Traits\QueryGenerator;
use App\Traits\RepositoryTrait;

/**
 * CounterRepository
 *
 * This repository provides a base implementation for Counter data access.
 * You can override or extend this class to customize query logic or add new methods.
 */
class CounterRepository implements CounterRepositoryInterface
{
    use RepositoryTrait;

    // The Category model instance.
    protected Counter $model;
    
    /**
     * Constructor.
     *
     * @param Counter $model The Counter model instance.
     * You can override this constructor in a child class if needed.
     */
    public function __construct(Counter $model)
    {
        $this->model = $model;
    }

    // You can override or add methods here to customize repository
}
