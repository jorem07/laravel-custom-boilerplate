<?php

namespace App\Services;

use App\DTO\BaseDTO;
use App\DTO\Office\OfficeDTO;
use App\Repositories\Contracts\OfficeRepositoryInterface;
use App\Traits\ServiceTrait;
use Illuminate\Support\Facades\DB;

class OfficeService extends BaseService
{

    use ServiceTrait;

    protected OfficeRepositoryInterface $repository;

    public function __construct(OfficeRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }
}
