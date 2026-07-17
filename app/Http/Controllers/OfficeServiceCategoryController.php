<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use App\Http\Requests\OfficeServiceCategory\Index;
use App\Http\Requests\OfficeServiceCategory\Show;
use App\Http\Requests\OfficeServiceCategory\Store;
use App\Http\Requests\OfficeServiceCategory\Update;
use App\Http\Requests\OfficeServiceCategory\Delete;
use App\Services\OfficeServiceCategoryService;

class OfficeServiceCategoryController extends Controller
{
    protected OfficeServiceCategoryService $officeServiceCategoryService;

    protected array $searchable = [];

    protected array $relation = [];

    public function __construct(OfficeServiceCategoryService $officeServiceCategoryService)
    {
         $this->officeServiceCategoryService = $officeServiceCategoryService;
    }

    public function index(Index $request) : JsonResponse
    {
        $payload = $request->validated();
        $data = $this->officeServiceCategoryService->index($payload, $this->searchable, $this->relation);

        return $this->getJsonResponse($data);
    }

    public function show($id, Show $request) : JsonResponse
    {
        $payload = $request->validated();
        $data = $this->officeServiceCategoryService->show($id, $payload, $this->relation);

        return $this->getJsonResponse($data);
    }

    public function store(Store $request) : JsonResponse
    {
        $payload = $request->validated();
        $data = $this->officeServiceCategoryService->store($payload, $this->relation);

        return $this->getJsonResponse($data);
    }

    public function update($id, Update $request) : JsonResponse
    {
        $payload = $request->validated();
        $data = $this->officeServiceCategoryService->update($id, $payload, $this->relation);

        return $this->getJsonResponse($data);
    }

    public function delete($id, Delete $request) : JsonResponse
    {
        $payload = $request->validated();
        $data = $this->officeServiceCategoryService->delete($id, $payload);

        return $this->getJsonResponse($data);
    }
}
