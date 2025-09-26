<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Repositories\RoleRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RoleController extends Controller
{
    protected RoleRepository $roleRepository;
    public function __construct(RoleRepository $roleRepository)
    {
        $this->roleRepository = $roleRepository;
    }
    public function index(Request $request) : JsonResponse
    {
        $data = $this->roleRepository->index($request);
        
        return response()->json(compact('data'));
    }

    public function store(Request $request) : JsonResponse
    {
        $payload = $request->validate(['name' => 'required|unique:roles,name']);
        $data = $this->roleRepository->store($payload);
        return response()->json(compact('data'));
    }

    public function show($id) : JsonResponse
    {
        $payload = validator(
                        ['id' => $id],
                        ['id' => 'required|integer|exists:roles,id']
                    )->validate();
        
        $data = $this->roleRepository->show($payload['id']);

        return response()->json(compact('data'));
    }

    public function update($id, Request $request) : JsonResponse
    {
        $payload = $request->validate([
                            'ability_id' => 'nullable|array|exists:abilities,id',
                            'name'       => 'nullable|unique:roles,name',
                            'title'      => 'required_with:name|unique:roles,title',
                        ]);

        $data = $this->roleRepository->update($id, $payload);

        return response()->json(compact('data'));
    }
}
