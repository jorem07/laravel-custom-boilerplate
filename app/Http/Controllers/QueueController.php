<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use App\Http\Requests\Queue\Index;
use App\Http\Requests\Queue\Show;
use App\Http\Requests\Queue\Store;
use App\Http\Requests\Queue\Update;
use App\Http\Requests\Queue\Delete;
use App\Http\Requests\Queue\Next;
use App\Http\Requests\Queue\Current;
use App\Services\QueueService;

class QueueController extends Controller
{
    protected QueueService $queueService;

    protected array $searchable = [];

    protected array $relation = [
        'queue_status' => ['id', 'name'],
        'counter' => ['id', 'name'],
        'user' => ['id', 'first_name', 'last_name'],
    ];

    public function __construct(QueueService $queueService)
    {
         $this->queueService = $queueService;
    }

    public function index(Index $request) : JsonResponse
    {
        $payload = $request->validated();
        $data = $this->queueService->index($payload, $this->searchable, $this->relation);

        return $this->getJsonResponse($data);
    }

    public function show($id, Show $request) : JsonResponse
    {
        $payload = $request->validated();
        $data = $this->queueService->show($id, $payload, $this->relation);

        return $this->getJsonResponse($data);
    }

    public function store(Store $request) : JsonResponse
    {
        $payload = $request->validated();
        $data = $this->queueService->store($payload, $this->relation);

        return $this->getJsonResponse($data);
    }

    public function update($id, Update $request) : JsonResponse
    {
        $payload = $request->validated();
        $data = $this->queueService->update($id, $payload, $this->relation);

        return $this->getJsonResponse($data);
    }

    public function delete($id, Delete $request) : JsonResponse
    {
        $payload = $request->validated();
        $data = $this->queueService->delete($id, $payload);

        return $this->getJsonResponse($data);
    }

    public function current(Current $request): JsonResponse
    {
        $data = $this->queueService->current($this->relation);

        return $this->getJsonResponse($data);
    }

    public function next(Next $request): JsonResponse
    {
        $payload = $request->validated();
        $data = $this->queueService->next($payload, $this->relation);

        return $this->getJsonResponse($data);
    }
}
