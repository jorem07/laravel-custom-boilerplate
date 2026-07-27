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

            $email = is_array($request) ? ($request['email'] ?? '') : $request->input('email');
            $password = is_array($request) ? ($request['password'] ?? '') : $request->input('password');
            $ip = is_array($request) ? '127.0.0.1' : $request->ip();

            $key = $email . '|' . $ip;
            if (RateLimiter::tooManyAttempts($key, 10)) {
                $seconds = RateLimiter::availableIn($key);
                return [
                    'message' => 'Too many attempts. Try again in ' . ceil($seconds / 60) . ' minutes.',
                    'errors' => 'RateLimited'
                ];
            }

            $user = User::where('email', $email)->first();

            if ($user) {
                if (strtolower($user->status) === 'inactive' || !$user->allow_login) {
                    return [
                        'message' => 'Your account is deactivated by the admin.',
                        'errors' => 'AccountDeactivated'
                    ];
                }

                if (Hash::check($password, $user->password)) {
                    RateLimiter::clear($key);

                    $user_agent = is_array($request) ? 'CLI' : $request->header('User-Agent');
                    $ip_address = is_array($request) ? '127.0.0.1' : $request->ip();

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
        $payload['status'] = 'Inactive';
        $payload['allow_login'] = false;
        
        $data = User::create($payload);

        $data->notify(new \App\Notifications\VerifyOTP());

        return $data;
    }

    public function resend($payload)
    {
        $data = User::where('email', $payload['email'])->first();

        $data->notify(new \App\Notifications\VerifyOTP());

        return ['message' => 'Email sent successfully!'];
    }

    public function verifyOtp($request)
    {

        $cachedOtp = Cache::get('email_otp_'.$request['user_id']);

        if (!$cachedOtp) {
            return response()->json(['message' => 'OTP expired or not found'], 400);
        }

        if ($cachedOtp != $request['otp']) {
            return response()->json(['message' => 'Invalid OTP'], 400);
        }

        // Mark email verified
        $user = User::find($request['user_id']);
        
        $user->update([
            'status' => true,
            'active' => true
        ]);

        $user->markEmailAsVerified();
        $user->assign('customer');

        // Clear OTP
        Cache::forget('email_otp_'.$request['user_id']);

        return response()->json(['message' => 'Email verified successfully']);
    }
}
