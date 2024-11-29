<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AddTokenFromCookie
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->hasCookie('authToken');

        if ($token) {
            $request->headers->set('Authorization', 'Bearer ' . $request->cookie('authToken'));
        } else {
            \Log::info('No token found in cookie');
        }

        return $next($request);
    }
}
