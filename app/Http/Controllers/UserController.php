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
}
