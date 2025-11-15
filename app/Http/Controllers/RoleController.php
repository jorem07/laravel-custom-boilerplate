<?php

namespace App\Http\Controllers;

use App\Repositories\RoleRepository;
use Illuminate\Http\JsonResponse;
use App\Http\Requests\Role\Index;
use App\Http\Requests\Role\Show;
use App\Http\Requests\Role\Store;
use App\Http\Requests\Role\Update;
use App\Http\Requests\Role\Delete;

class RoleController extends Controller
{
    protected RoleRepository $roleRepository;

    protected array $searchable = [];

    protected array $relation = [];

    public function __construct(RoleRepository $roleRepository)
    {
         $this->roleRepository = $roleRepository;
    }

    public function index(Index $request) : JsonResponse
    {
        $payload = $request->validated();
        $data = $this->roleRepository->index($payload, $this->searchable, $this->relation);

        return $this->getJsonResponse($data);
    }

    public function show($id, Show $request) : JsonResponse
    {
        $payload = $request->validated();
        $data = $this->roleRepository->show($id, $payload, $this->relation);

        return $this->getJsonResponse($data);
    }

    public function store(Store $request) : JsonResponse
    {
        $payload = $request->validated();
        $data = $this->roleRepository->store($payload, $this->relation);

        return $this->getJsonResponse($data);
    }

    public function update($id, Update $request) : JsonResponse
    {
        $payload = $request->validated();
        $data = $this->roleRepository->update($id, $payload, $this->relation);

        return $this->getJsonResponse($data);
    }

    public function delete($id, Delete $request) : JsonResponse
    {
        $payload = $request->validated();
        $data = $this->roleRepository->delete($id, $payload);

        return $this->getJsonResponse($data);
    }
}
