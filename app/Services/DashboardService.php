<?php

namespace App\Services;

use App\Repositories\UserRepository;

class DashboardService
{
    protected UserRepository $userRepository;

    public function __construct
    (
        UserRepository $userRepository
    )
    {
        $this->userRepository = $userRepository;
    }

    public function getTotalData() : array
    {
        $data = [
            'user' => $this->userRepository->index()['total'] ?? 0
        ];

        return $data;
    }
}