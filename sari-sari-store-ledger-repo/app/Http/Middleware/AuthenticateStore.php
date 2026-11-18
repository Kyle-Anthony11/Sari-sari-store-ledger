<?php

namespace App\Http\Middleware;

use App\Services\AuthService;
use Closure;
use Illuminate\Http\Request;

class AuthenticateStore
{
    public function handle(Request $request, Closure $next)
    {
        if (!AuthService::check()) {
            return redirect()->route('login');
        }

        return $next($request);
    }
}
