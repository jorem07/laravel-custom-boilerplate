<?php

namespace App\Http\Controllers;

use App\Http\Requests\Announcement\Delete;
use App\Http\Requests\Announcement\Index;
use App\Http\Requests\Announcement\Show;
use App\Http\Requests\Announcement\Store;
use App\Http\Requests\Announcement\Update;
use App\Services\AnnouncementService;
use Illuminate\Http\JsonResponse;

class AnnouncementController extends Controller
{
    protected AnnouncementService $announcementService;

    protected array $searchable = [];

    protected array $relation = [
        'created_by_user' => ['id', 'fullname'],
    ];

    public function __construct(AnnouncementService $announcementService)
    {
        $this->announcementService = $announcementService;
    }

    public function index(Index $request): JsonResponse
    {
        $payload = $request->validated();
        $data = $this->announcementService->index($payload, $this->searchable, $this->relation);

        return $this->getJsonResponse($data);
    }

    public function show($id, Show $request): JsonResponse
    {
        $payload = $request->validated();
        $data = $this->announcementService->show($id, $payload, $this->relation);

        return $this->getJsonResponse($data);
    }

    public function store(Store $request): JsonResponse
    {
        $payload = $request->validated();
        $data = $this->announcementService->store($payload, $this->relation);

        return $this->getJsonResponse($data);
    }

    public function update($id, Update $request): JsonResponse
    {
        $payload = $request->validated();
        $data = $this->announcementService->update($id, $payload, $this->relation);

        return $this->getJsonResponse($data);
    }

    public function delete($id, Delete $request): JsonResponse
    {
        $payload = $request->validated();
        $data = $this->announcementService->delete($id, $payload);

        return $this->getJsonResponse($data);
    }
}
