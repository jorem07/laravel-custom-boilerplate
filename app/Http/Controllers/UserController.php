<?php

namespace App\Http\Controllers;


use App\Repositories\UserRepository;
use Illuminate\Http\JsonResponse;
use App\Http\Requests\User\Delete;
use App\Http\Requests\User\Index;
use App\Http\Requests\User\Show;
use App\Http\Requests\User\Store;
use App\Http\Requests\User\Update;

class UserController extends Controller
{
    protected UserRepository $userRepository;
    
    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    public function index(Index $request) : JsonResponse
    {
        $payload = $request->validated();

        $data = $this->userRepository->index($payload);

        return $this->userRepository->getJsonResponse($data);
    }

    public function store(Store $request) : JsonResponse
    {
        $payload = $request->validated();

        $data = $this->userRepository->store($payload);

        return $this->userRepository->getJsonResponse($data);
    }

    public function show($id, Show $request) : JsonResponse
    {
        $payload = $request->validated();
        
        $data = $this->userRepository->show($payload['id']);

        return $this->userRepository->getJsonResponse($data);
    }

    public function update($id, Update $request) : JsonResponse
    {
        
        $payload = $request->validated();
        
        $data = $this->userRepository->update($id, $payload);
        
        return $this->userRepository->getJsonResponse($data);
    }

    public function delete($id, Delete $request) : JsonResponse
    {
        $payload = $request->validated();
        $data = $this->userRepository->delete($id);
        
        return $this->userRepository->getJsonResponse($data);
    }
}
