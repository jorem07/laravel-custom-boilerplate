<?php

namespace App\Http\Controllers;
 
use App\Http\Requests\Display\Delete;
use App\Http\Requests\Display\Index;
use App\Http\Requests\Display\Show;
use App\Http\Requests\Display\Store;
use App\Http\Requests\Display\Update;
use App\Services\DisplayService;
use Illuminate\Http\JsonResponse;

class DisplayController extends Controller
{
    protected DisplayService $displayService;

    protected array $searchable = [];

    protected array $relation = [
        'office' => ['id', 'name'],
    ];

    public function __construct(DisplayService $displayService)
    {
        $this->displayService = $displayService;
    }

    public function index(Index $request): JsonResponse
    {
        $payload = $request->validated();
        $data = $this->displayService->index($payload, $this->searchable, $this->relation);

        return $this->getJsonResponse($data);
    }

    public function show($id, Show $request): JsonResponse
    {
        $payload = $request->validated();
        $data = $this->displayService->show($id, $payload, $this->relation);

        return $this->getJsonResponse($data);
    }

    public function store(Store $request): JsonResponse
    {
        $payload = $request->validated();
        $data = $this->displayService->store($payload, $this->relation);

        return $this->getJsonResponse($data);
    }

    public function update($id, Update $request): JsonResponse
    {
        $payload = $request->validated();
        $data = $this->displayService->update($id, $payload, $this->relation);

        return $this->getJsonResponse($data);
    }

    public function delete($id, Delete $request): JsonResponse
    {
        $payload = $request->validated();
        $data = $this->displayService->delete($id, $payload);

        return $this->getJsonResponse($data);
    }
}
