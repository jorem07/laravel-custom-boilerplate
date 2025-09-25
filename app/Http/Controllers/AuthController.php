<?php

namespace App\Http\Controllers;

use App\Http\Requests\Auth\Login;
use App\Repositories\AuthRepository;
use App\Services\AuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    protected AuthService $AuthService;
    public function __construct(AuthService $AuthService)
    {
        $this->AuthService = $AuthService;
    }
    
    public function login(Request $request): JsonResponse
    {
        $data = $this->AuthService->login($request);
        
        if(isset($data['errors']))
            $status = 422;
        else
            $status = 200;

        return response()->json($data, $status);
    }

    public function logout(Request $request): string
    {
        $data = $this->authRepository->logout($request);
        return response()->json($data);
    }

    public function register(Request $request) : JsonResponse
    {
        $payload = $request->all();
        $data = $this->AuthService->register($payload);

        return $data;
    }
}
