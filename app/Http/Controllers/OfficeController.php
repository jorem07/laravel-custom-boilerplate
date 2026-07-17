<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use App\Http\Requests\Office\Index;
use App\Http\Requests\Office\Show;
use App\Http\Requests\Office\Store;
use App\Http\Requests\Office\Update;
use App\Http\Requests\Office\Delete;
use App\Services\OfficeService;

class OfficeController extends Controller
{
    protected OfficeService $officeService;

    protected array $searchable = [];

    protected array $relation = [];

    public function __construct(OfficeService $officeService)
    {
         $this->officeService = $officeService;
    }

    public function index(Index $request) : JsonResponse
    {
        $payload = $request->validated();
        $data = $this->officeService->index($payload, $this->searchable, $this->relation);

        return $this->getJsonResponse($data);
    }

    public function show($id, Show $request) : JsonResponse
    {
        $payload = $request->validated();
        $data = $this->officeService->show($id, $payload, $this->relation);

        return $this->getJsonResponse($data);
    }

    public function store(Store $request) : JsonResponse
    {
        $payload = $request->validated();
        $data = $this->officeService->store($payload, $this->relation);

        return $this->getJsonResponse($data);
    }

    public function update($id, Update $request) : JsonResponse
    {
        $payload = $request->validated();
        $data = $this->officeService->update($id, $payload, $this->relation);

        return $this->getJsonResponse($data);
    }

    public function delete($id, Delete $request) : JsonResponse
    {
        $payload = $request->validated();
        $data = $this->officeService->delete($id, $payload);

        return $this->getJsonResponse($data);
    }
}
