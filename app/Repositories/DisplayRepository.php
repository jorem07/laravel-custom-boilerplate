<?php

namespace App\Repositories;

use App\Models\Display;
use App\Repositories\Contracts\DisplayRepositoryInterface;
use App\Traits\RepositoryTrait;

class DisplayRepository implements DisplayRepositoryInterface
{
    use RepositoryTrait;

    protected Display $model;

    public function __construct(Display $model)
    {
        $this->model = $model;
    }
}
