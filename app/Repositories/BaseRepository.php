<?php
namespace App\Repositories;

abstract class BaseRepository
{
    // The model instance.
    protected $model;

    /**
     * Constructor.
     *
     * @param mixed $model The model instance.
     * You can override this constructor in a child class if needed.
     */
    public function __construct($model)
    {
        $this->model = $model;
    }

    // You can add common repository methods here to be shared across all repositories
}