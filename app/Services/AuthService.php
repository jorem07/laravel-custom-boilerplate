<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AuthService
{
    public function login($request): array
    {
        try {
            if (Auth::attempt($request->only('email', 'password'))) {

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

                    return ([
                        'message' => 'Logged in successfully.',
                        'user' => $user->load(['roles']),
                        'token' => $token->plainTextToken,
                    ]);
                } else {
                    # Return verify account message if account is not verified
                    return ([
                        'message' => 'Please verify your account first.',
                        'errors' => 'Error'
                    ]);
                }
            }

            # Return vague error message to prevent brute force attack
            return ([
                'message' => 'Invalid login credentials.',
                'errors' => 'Error'
            ]);
        } catch (\Throwable $throwable) {
            return ([
                'message' => $throwable->getMessage(),
                'errors' => 'Error'
            ]);
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
