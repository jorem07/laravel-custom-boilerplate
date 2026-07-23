<?php

namespace App\Http\Controllers;

use App\Http\Requests\Counter\Active;
use App\Http\Requests\Counter\Delete;
use App\Http\Requests\Counter\Index;
use App\Http\Requests\Counter\Login;
use App\Http\Requests\Counter\Logout;
use App\Http\Requests\Counter\Logs;
use App\Http\Requests\Counter\Performance;
use App\Http\Requests\Counter\Show;
use App\Http\Requests\Counter\Store;
use App\Http\Requests\Counter\Update;
use App\Services\CounterPerformanceService;
use App\Services\CounterService;
use Illuminate\Http\JsonResponse;

class CounterController extends Controller
{
    protected CounterService $counterService;

    protected array $searchable = [];

    protected array $relation = [
        'user' => ['id', 'first_name', 'last_name'],
        'office_service' => ['id', 'name'],
    ];

    public function __construct(
        CounterService $counterService,
        protected CounterPerformanceService $counterPerformanceService,
    ) {
        $this->counterService = $counterService;
    }

    public function index(Index $request): JsonResponse
    {
        $payload = $request->validated();
        $data = $this->counterService->index($payload, $this->searchable, $this->relation);

        return $this->getJsonResponse($data);
    }

    public function show($id, Show $request): JsonResponse
    {
        $payload = $request->validated();
        $data = $this->counterService->show($id, $payload, $this->relation);

        return $this->getJsonResponse($data);
    }

    public function store(Store $request): JsonResponse
    {
        $payload = $request->validated();
        $data = $this->counterService->store($payload, $this->relation);

        return $this->getJsonResponse($data);
    }

    public function update($id, Update $request): JsonResponse
    {
        $payload = $request->validated();
        $data = $this->counterService->update($id, $payload, $this->relation);

        return $this->getJsonResponse($data);
    }

    public function delete($id, Delete $request): JsonResponse
    {
        $payload = $request->validated();
        $data = $this->counterService->delete($id, $payload);

        return $this->getJsonResponse($data);
    }

    public function active(Active $request): JsonResponse
    {
        $data = $this->counterService->active($this->relation);

        return $this->getJsonResponse($data);
    }

    public function logs(Logs $request): JsonResponse
    {
        $data = $this->counterService->logs();

        return $this->getJsonResponse($data);
    }

    public function login(Login $request): JsonResponse
    {
        $payload = $request->validated();
        $data = $this->counterService->login($payload, $this->relation);

        return $this->getJsonResponse($data);
    }

    public function logout(Logout $request): JsonResponse
    {
        $payload = $request->validated();
        $data = $this->counterService->logout($payload, $this->relation);

        return $this->getJsonResponse($data);
    }

    public function performance(Performance $request): JsonResponse
    {
        $payload = $request->validated();
        $data = $this->counterPerformanceService->getTopPerformances(
            $payload['office_service_id'] ?? null,
            $payload['limit'] ?? 5,
        );

        return $this->getJsonResponse($data);
    }
}
