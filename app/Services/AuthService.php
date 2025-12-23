<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;

class AuthService
{
    public function login($request): array
    {
        try {

            // Checking for attempts
            $key = $request['email'] . '|' . $request['ip'];
            if (RateLimiter::tooManyAttempts($key, 5)) {
                $seconds = RateLimiter::availableIn($key);
                return [
                    'message' => 'Too many attempts. Try again in ' . ceil($seconds / 60) . ' minutes.',
                    'errors' => 'RateLimited'
                ];
            }

            if (Auth::attempt($request->only('email', 'password'))) {

                // Clear attempts after successful login
                RateLimiter::clear($key);

                $user = Auth::user();

                if ($user->status && $user->allow_login) {
                    $user_agent = $request->header('User-Agent');
                    $ip_address = $request->ip();

                    $token = $user->createToken('auth-token');

                    DB::table('personal_access_tokens')->where('id', $token->accessToken->id)
                        ->update([
                            'user_agent' => $user_agent,
                            'ip_address' => $ip_address
                        ]);

                    return [
                        'message' => 'Logged in successfully.',
                        'user' => $user->load(['roles']),
                        'token' => $token->plainTextToken,
                    ];
                }

                return [
                    'message' => 'Please verify your account first.',
                    'errors' => 'Error'
                ];
            }

            # here is the failed attempt lockout 
            RateLimiter::hit($key, 300); #5 minutes

            return [
                'message' => 'Invalid login credentials.',
                'errors' => 'Error'
            ];

        } catch (\Throwable $throwable) {
            return [
                'message' => $throwable->getMessage(),
                'errors' => 'Error'
            ];
        }
    }

    public function logout($request): array
    {
        $message = 'Successfully logged out';

        if ($request->logout == 'others') {
            $request->user()->tokens
                ->where('id', '<>', $request->user()->currentAccessToken()->id)
                ->each(function ($token) {
                    $token->update(['expires_at' => now()]);
                });
            $message = 'Successfully logged out other devices.';
        } elseif ($request->logout == 'all') {
            $request->user()->tokens->each(function ($token) {
                $token->update(['expires_at' => now()]);
            });
            $message = 'Successfully logged out all devices.';
        } else {
            $request->user()->currentAccessToken()->update([
                'expires_at' => now(),
            ]);
            $message = 'Successfully logged out.';
        }

        return (['message' => $message]);
    }

    public function register($payload)
    {
        // TODO: User register with Email OTP
        $payload['password'] = Hash::make($payload['password']);
        $payload['status'] = false;
        $payload['allow_login'] = false;
        
        $data = User::where('email', $payload['email'])->first();
        if ($data) {
            if (is_null($data->email_verified_at)) {
                
                $data->sendEmailVerificationNotification();
                return [
                    'message' => 'The email already exists. We resent your verification email.',
                    'status' => 200,
                    'errors'   => [
                        'email' => 'Email is already used.',
                    ],
                ];
            }

            return [
                'message' => 'Email already registered and verified.',
                'errors'   => [
                    'email' => 'Email is already used.',
                ],
                'status' => 422];
        }

        $data = User::create($payload);

        $data->sendEmailVerificationNotification();

        return ['message' => 'Email sent successfully!', 'status' => 200];
    }

    
    public function resend($payload)
{
    $user = User::where('email', $payload['email'])->first();

    if (!$user) {
        return ['message' => 'User not found'];
    }

    $user->notify(new \App\Notifications\VerifyOTP());

    return ['message' => 'OTP email sent successfully!'];
}


    public function verifyOtp($request)
{
    $user = User::where('email', $request['email'])->first();

    if (!$user) {
        return response()->json(['message' => 'User not found'], 404);
    }

    $cachedOtp = Cache::get('email_otp_'.$user->id);

    if (!$cachedOtp) {
        return response()->json(['message' => 'OTP expired or not found'], 400);
    }

    if ($cachedOtp != $request['otp']) {
        return response()->json(['message' => 'Invalid OTP'], 400);
    }

    // Mark email verified
    $user->update([
        'status' => true,
        'active' => true
    ]);

    $user->markEmailAsVerified();
    $user->assign('customer');

    // Clear OTP
    Cache::forget('email_otp_'.$user->id);

    return response()->json(['message' => 'Email verified successfully']);
}

}
