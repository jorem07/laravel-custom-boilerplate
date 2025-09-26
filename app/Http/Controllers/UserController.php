<?php

namespace App\Http\Controllers;

use App\Repositories\UserRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserController extends Controller
{
    protected UserRepository $userRepository;
    
    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    public function index(Request $request) : JsonResponse
    {
        $payload = $request->all();

        $data = $this->userRepository->index($payload);

        return response()->json(compact('data'));
    }

    public function store(Request $request) : JsonResponse
    {
        $payload = $request->validate([
            
            "first_name"    =>  "required",
            "last_name"     =>  "required",
            "middle_name"   =>  "nullable",
            "email"         =>  "required|unique:users",
            "allow_login"   =>  "required",
            "status"        =>  "required",
            "password"      =>  "required",
            "role"          =>  "required|exists:roles,name"
        
        ]);

        $data = $this->userRepository->store($payload);

        return response()->json(compact('data'));
    }

    public function show($id) : JsonResponse
    {
        $payload = validator(
                        ['id' => $id],
                        ['id' => 'required|integer|exists:users,id']
                    )->validate();
        
        $data = $this->userRepository->show($payload['id']);

        return response()->json(compact('data'));
    }

    public function update($id, Request $request) : JsonResponse
    {
        
        $payload = $request->validate([
                        "first_name"    =>  "nullable",
                        "last_name"     =>  "nullable",
                        "middle_name"   =>  "nullable",
                        "email"         =>  "nullable|unique:users",
                        "allow_login"   =>  "nullable",
                        "status"        =>  "nullable",
                        "password"      =>  "nullable",
                        "role"          =>  "nullable|exists:roles,name"
        ]);
        
        $data = $this->userRepository->update($id, $payload);
        
        return response()->json(compact('data'));
    }

    public function delete($id) : JsonResponse
    {
        $payload = validator(
                        ['id' => $id],
                        ['id' => 'required|integer|exists:users,id']
                    )->validate();

        $data = $this->userRepository->delete($id);
        return response()->json(compact('data'));
    }
}
