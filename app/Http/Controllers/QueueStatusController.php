<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use App\Http\Requests\QueueStatus\Index;
use App\Http\Requests\QueueStatus\Show;
use App\Http\Requests\QueueStatus\Store;
use App\Http\Requests\QueueStatus\Update;
use App\Http\Requests\QueueStatus\Delete;
use App\Services\QueueStatusService;

class QueueStatusController extends Controller
{
    protected QueueStatusService $queueStatusService;

    protected array $searchable = [];

    protected array $relation = [];

    public function __construct(QueueStatusService $queueStatusService)
    {
         $this->queueStatusService = $queueStatusService;
    }

    public function index(Index $request) : JsonResponse
    {
        $payload = $request->validated();
        $data = $this->queueStatusService->index($payload, $this->searchable, $this->relation);

        return $this->getJsonResponse($data);
    }

    public function show($id, Show $request) : JsonResponse
    {
        $payload = $request->validated();
        $data = $this->queueStatusService->show($id, $payload, $this->relation);

        return $this->getJsonResponse($data);
    }

    public function store(Store $request) : JsonResponse
    {
        $payload = $request->validated();
        $data = $this->queueStatusService->store($payload, $this->relation);

        return $this->getJsonResponse($data);
    }

    public function update($id, Update $request) : JsonResponse
    {
        $payload = $request->validated();
        $data = $this->queueStatusService->update($id, $payload, $this->relation);

        return $this->getJsonResponse($data);
    }

    public function delete($id, Delete $request) : JsonResponse
    {
        $payload = $request->validated();
        $data = $this->queueStatusService->delete($id, $payload);

        return $this->getJsonResponse($data);
    }
}
