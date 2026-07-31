<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use App\Http\Requests\Role\Index;
use App\Http\Requests\Role\Show;
use App\Http\Requests\Role\Store;
use App\Http\Requests\Role\Update;
use App\Http\Requests\Role\Delete;
use App\Services\RoleService;
use App\Http\Requests\Ability\Index as AbilityIndex;

class RoleController extends Controller
{
    protected RoleService $roleService;

    protected array $searchable = [];

    protected array $relation = [];

    public function __construct(RoleService $roleService)
    {
         $this->roleService = $roleService;
    }

    public function index(Index $request) : JsonResponse
    {
        $payload = $request->validated();
        $data = $this->roleService->index($payload, $this->searchable, $this->relation);

        return $this->getJsonResponse($data);
    }

    public function show($id, Show $request) : JsonResponse
    {
        $payload = $request->validated();
        $data = $this->roleService->show($id, $payload, $this->relation);

        return $this->getJsonResponse($data);
    }

    public function store(Store $request) : JsonResponse
    {
        $payload = $request->validated();
        $data = $this->roleService->store($payload, $this->relation);

        return $this->getJsonResponse($data);
    }

    public function update($id, Update $request) : JsonResponse
    {
        $payload = $request->validated();
        $data = $this->roleService->update($id, $payload, $this->relation);

        return $this->getJsonResponse($data);
    }

    public function delete($id, Delete $request) : JsonResponse
    {
        $payload = $request->validated();
        $data = $this->roleService->delete($id, $payload);

        return $this->getJsonResponse($data);
    }

    public function getAllAbilities(AbilityIndex $request) : JsonResponse
    {
        $payload = $request->validated();
        $data = $this->roleService->getAllAbilities($payload, $this->searchable, $this->relation);
        return $this->getJsonResponse($data);
    }
}
