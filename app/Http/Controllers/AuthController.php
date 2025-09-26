<?php

namespace App\Http\Controllers;

use App\Services\AuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;

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
        $data = $this->AuthService->logout($request);
        return response()->json($data);
    }

    public function register(Request $request) : JsonResponse
    {
        $payload = $request->validate([
            "first_name"    =>  "required",
            "last_name"     =>  "required",
            "middle_name"   =>  "nullable",
            "email"         =>  "required|unique:users",
            "password"      =>  "required"
        ]);
        
        $data = $this->AuthService->register($payload);

        return response()->json(compact('data'));
    }

    public function resend(Request $request) : JsonResponse
    {
        $payload = $request->validate([
            "email"         =>  "required|exists:users"
        ]);
        
        $data = $this->AuthService->resend($payload);

        return response()->json(compact('data'));
    }

    public function verifyOtp(Request $request) : JsonResponse
    {
        $payload = $request->validate([
            'user_id' => 'required|exists:users,id',
            'otp' => 'required|numeric'
        ]);
        
        $data = $this->AuthService->verifyOtp($payload);

        return response()->json(compact('data'));
    }

    public function forgotPassword(Request $request) : JsonResponse
    {
        $request->validate(['email' => 'required|email']);

        $status = Password::sendResetLink(

            $request->only('email')

        );

        return response()->json(['message' => 'Email sent successfully!']);
    }
}
