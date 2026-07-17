<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use App\Http\Requests\OfficeService\Index;
use App\Http\Requests\OfficeService\Show;
use App\Http\Requests\OfficeService\Store;
use App\Http\Requests\OfficeService\Update;
use App\Http\Requests\OfficeService\Delete;
use App\Services\OfficeServiceService;

class OfficeServiceController extends Controller
{
    protected OfficeServiceService $officeServiceService;

    protected array $searchable = [];

    protected array $relation = [
        'office' => ['id', 'name']
    ];

    public function __construct(OfficeServiceService $officeServiceService)
    {
         $this->officeServiceService = $officeServiceService;
    }

    public function index(Index $request) : JsonResponse
    {
        $payload = $request->validated();
        $data = $this->officeServiceService->index($payload, $this->searchable, $this->relation);

        return $this->getJsonResponse($data);
    }

    public function show($id, Show $request) : JsonResponse
    {
        $payload = $request->validated();
        $data = $this->officeServiceService->show($id, $payload, $this->relation);

        return $this->getJsonResponse($data);
    }

    public function store(Store $request) : JsonResponse
    {
        $payload = $request->validated();
        $data = $this->officeServiceService->store($payload, $this->relation);

        return $this->getJsonResponse($data);
    }

    public function update($id, Update $request) : JsonResponse
    {
        $payload = $request->validated();
        $data = $this->officeServiceService->update($id, $payload, $this->relation);

        return $this->getJsonResponse($data);
    }

    public function delete($id, Delete $request) : JsonResponse
    {
        $payload = $request->validated();
        $data = $this->officeServiceService->delete($id, $payload);

        return $this->getJsonResponse($data);
    }
}
