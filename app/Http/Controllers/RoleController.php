<?php

namespace App\Http\Controllers;

use App\Http\Requests\Role\Delete;
use App\Http\Requests\Role\Index;
use App\Http\Requests\Role\Show;
use App\Http\Requests\Role\Store;
use App\Http\Requests\Role\Update;

use App\Repositories\RoleRepository;
use Illuminate\Http\JsonResponse;

class RoleController extends Controller
{
    protected RoleRepository $roleRepository;
    protected array $searchable = ['name'];

    public function __construct(RoleRepository $roleRepository)
    {
        $this->roleRepository = $roleRepository;
    }
    
    public function index(Index $request) : JsonResponse
    {
        $payload = $request->validated();
        $data = $this->roleRepository->index($payload, $this->searchable);
        return $this->roleRepository->getJsonResponse($data);
    }

    public function store(Store $request) : JsonResponse
    {
        $payload = $request->validate(['name' => 'required|unique:roles,name']);
        $data = $this->roleRepository->store($payload);
        return $this->roleRepository->getJsonResponse($data);
    }

    public function show($id, Show $request) : JsonResponse
    {
        $payload = $request->validated();
        
        $data = $this->roleRepository->show($id);

        return $this->roleRepository->getJsonResponse($data);
    }

    public function update($id, Update $request) : JsonResponse
    {
        $payload = $request->validated();

        $data = $this->roleRepository->update($id, $payload);

        return $this->roleRepository->getJsonResponse($data);
    }

    public function delete($id, Delete $request) : JsonResponse
    {
        $payload = $request->validated();
        
        $data = $this->roleRepository->delete($id);

        return $this->roleRepository->getJsonResponse($data);
    }
}
