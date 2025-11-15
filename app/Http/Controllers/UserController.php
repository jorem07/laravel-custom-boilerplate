<?php

namespace App\Http\Controllers;

use App\Repositories\UserRepository;
use Illuminate\Http\JsonResponse;
use App\Http\Requests\User\Index;
use App\Http\Requests\User\Show;
use App\Http\Requests\User\Store;
use App\Http\Requests\User\Update;
use App\Http\Requests\User\Delete;

class UserController extends Controller
{
    protected UserRepository $userRepository;

    protected array $searchable = [];

    protected array $relation = [
        'roles' => ['id', 'name']
    ];

    public function __construct(UserRepository $userRepository)
    {
         $this->userRepository = $userRepository;
    }

    public function index(Index $request) : JsonResponse
    {
        $payload = $request->validated();
        $data = $this->userRepository->index($payload, $this->searchable, $this->relation);

        return $this->getJsonResponse($data);
    }

    public function show($id, Show $request) : JsonResponse
    {
        $payload = $request->validated();
        $data = $this->userRepository->show($id, $payload, $this->relation);

        return $this->getJsonResponse($data);
    }

    public function store(Store $request) : JsonResponse
    {
        $payload = $request->validated();
        $data = $this->userRepository->store($payload, $this->relation);

        return $this->getJsonResponse($data);
    }

    public function update($id, Update $request) : JsonResponse
    {
        $payload = $request->validated();
        $data = $this->userRepository->update($id, $payload, $this->relation);

        return $this->getJsonResponse($data);
    }

    public function delete($id, Delete $request) : JsonResponse
    {
        $payload = $request->validated();
        $data = $this->userRepository->delete($id, $payload);

        return $this->getJsonResponse($data);
    }
}
