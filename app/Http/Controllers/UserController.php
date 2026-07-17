<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use App\Http\Requests\User\Index;
use App\Http\Requests\User\Show;
use App\Http\Requests\User\Store;
use App\Http\Requests\User\Update;
use App\Http\Requests\User\Delete;
use App\Services\UserService;

class UserController extends Controller
{
    protected UserService $userService;

    protected array $searchable = [];

    protected array $relation = [
        'roles' => ['id', 'name']
    ];

    public function __construct(UserService $userService)
    {
         $this->userService = $userService;
    }

    public function index(Index $request) : JsonResponse
    {
        $payload = $request->validated();
        $data = $this->userService->index($payload, $this->searchable, $this->relation);

        return $this->getJsonResponse($data);
    }

    public function show($id, Show $request) : JsonResponse
    {
        $payload = $request->validated();
        $data = $this->userService->show($id, $payload, $this->relation);

        return $this->getJsonResponse($data);
    }

    public function store(Store $request) : JsonResponse
    {
        $payload = $request->validated();
        $data = $this->userService->store($payload, $this->relation);

        return $this->getJsonResponse($data);
    }

    public function update($id, Update $request) : JsonResponse
    {
        $payload = $request->validated();
        $data = $this->userService->update($id, $payload, $this->relation);

        return $this->getJsonResponse($data);
    }

    public function delete($id, Delete $request) : JsonResponse
    {
        $payload = $request->validated();
        $data = $this->userService->delete($id, $payload);

        return $this->getJsonResponse($data);
    }
}
