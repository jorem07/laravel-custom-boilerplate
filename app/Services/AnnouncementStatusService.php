<?php

namespace App\Services;

use App\Repositories\Contracts\AnnouncementStatusRepositoryInterface;
use Illuminate\Support\Facades\DB;
use App\Traits\ServiceTrait;

class AnnouncementStatusService extends BaseService
{
    use ServiceTrait;

    protected AnnouncementStatusRepositoryInterface $repository;

    public function __construct(AnnouncementStatusRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    // You can override or add methods here to customize service
}
