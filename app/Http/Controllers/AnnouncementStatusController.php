<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use App\Http\Requests\AnnouncementStatus\Index;
use App\Http\Requests\AnnouncementStatus\Show;
use App\Http\Requests\AnnouncementStatus\Store;
use App\Http\Requests\AnnouncementStatus\Update;
use App\Http\Requests\AnnouncementStatus\Delete;
use App\Services\AnnouncementStatusService;

class AnnouncementStatusController extends Controller
{
    protected AnnouncementStatusService $announcementstatusService;

    protected array $searchable = [];

    protected array $relation = [];

    public function __construct(AnnouncementStatusService $announcementstatusService)
    {
         $this->announcementstatusService = $announcementstatusService;
    }

    /**
     * Get all data.
     */
    public function index(Index $request) : JsonResponse
    {
        $payload = $request->validated();
        $data = $this->announcementstatusService->index($payload, $this->searchable, $this->relation);

        return $this->getJsonResponse($data);
    }

    /**
     * Get specific data.
     * @param int $id
     */
    public function show($id, Show $request) : JsonResponse
    {
        $payload = $request->validated();
        $data = $this->announcementstatusService->show($id, $payload, $this->relation);

        return $this->getJsonResponse($data);
    }

    /**
     * Store data.
     */
    public function store(Store $request) : JsonResponse
    {
        $payload = $request->validated();
        $data = $this->announcementstatusService->store($payload, $this->relation);

        return $this->getJsonResponse($data);
    }

    /**
     * Update specific data.
     * @param int $id
     */
    public function update($id, Update $request) : JsonResponse
    {
        $payload = $request->validated();
        $data = $this->announcementstatusService->update($id, $payload, $this->relation);

        return $this->getJsonResponse($data);
    }

    /**
     * Delete specific data.
     * @param int $id
     */
    public function delete($id, Delete $request) : JsonResponse
    {
        $payload = $request->validated();
        $data = $this->announcementstatusService->delete($id, $payload);

        return $this->getJsonResponse($data);
    }

    // Add custom methods below
}
