<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

use Illuminate\Support\Facades\Log;

class LogRoute
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        $userId = $user ? $user->id : 'Guest';
        $email = $user ? $user->email : 'None';

        Log::info("API Request Logged", [
            'method'  => $request->method(),
            'uri'     => $request->getRequestUri(),
            'ip'      => $request->ip(),
            'user_id' => $userId,
            'email'   => $email,
        ]);

        return $next($request);
    }
}
