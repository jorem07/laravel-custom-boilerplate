<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\AuthService;
use App\Services\SecurityService;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Cache;

// TODO: Refactor
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
            // "email"         =>  ["required","email",new DisposableEmail()],
            "email"         => ['required', 'email'],
            "password"      =>  "required",
            "birthday"      =>  "required"
        ]);
        
        
        $data = $this->AuthService->register($payload);
        if(isset($data['errors'])){
            return response()->json([
                'message'=>$data['message'], 
                'errors' => $data['errors']
            ], $data['status']);
        }
        return response()->json([
            'message'=>$data['message'], 
        ], $data['status']);
    }

    public function forgotPassword(Request $request) : JsonResponse
    {
        $request->validate(['email' => 'required|email']);

        $status = Password::sendResetLink(

            $request->only('email')

        );

        return response()->json(['message' => 'Email sent successfully!']);
    }

    function verifyEmail($id, $hash) : JsonResponse
    {
        $user = User::findOrFail($id);

        if (! hash_equals((string) $hash, sha1($user->getEmailForVerification()))) {
            abort(403);
        }

        if (! $user->hasVerifiedEmail()) {
            $user->markEmailAsVerified();
            $user->update([
                'status'      => true,
                'allow_login' => true
            ]);

            $user->customerDetail([
                'user_id' => $user->id
            ]);

            $user->markEmailAsVerified();
            $user->assign('customer');
        }

        return response()->json(['message' => 'Email verified successfully.']);
    }

    public function resetPassword(Request $request): JsonResponse
    {
        $request->validate([
            'token' => 'required',
            'password' => 'required|min:8|confirmed',
        ]);

        $security = new SecurityService();
        $token = explode('##', $request->token);
        $email = $security->decrypt($token[1]);
        $request['email'] = $email;
        $request['token'] = $token[0];

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (User $user, string $password) {
                $user->forceFill([
                    'password' => Hash::make($password)
                ])->setRememberToken(Str::random(60));

                $user->save();

                event(new PasswordReset($user));
            }
        );

        if ($status === Password::PASSWORD_RESET) {
            return response()->json(['message' => 'Password has been successfully reset.']);
        }

        return response()->json([
            'message' => __($status)
        ], 400);
    }


    public function resend($payload)
    {
        $data = User::where('email', $payload['email'])->first();

        // $data->notify(new \App\Notifications\VerifyOTP());
        $data->sendEmailVerificationNotification();

        return ['message' => 'Email sent successfully!'];
    }

    public function verifyOtp($request)
    {

        $cachedOtp = Cache::get('email_otp_'.$request['email']);

        if (!$cachedOtp) {
            return response()->json(['message' => 'OTP expired or not found'], 400);
        }

        if ($cachedOtp != $request['otp']) {
            return response()->json(['message' => 'Invalid OTP'], 400);
        }

        // Mark email verified
        $user = User::where('email', $request['email'])->first();
        
        $user->update([
            'status'      => true,
            'allow_login' => true
        ]);

        $user->customerDetail([
            'user_id' => $user->id
        ]);

        $user->markEmailAsVerified();
        $user->assign('customer');

        // Clear OTP
        Cache::forget('email_otp_'.$request['email']);

        return response()->json(['message' => 'Email verified successfully']);
    }
}
