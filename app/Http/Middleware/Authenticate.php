<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Request;

class Authenticate extends Middleware
{
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     */
    protected function redirectTo($request)
    {
        // ✅ Instead of redirecting, return a JSON response for API requests
        if (!$request->expectsJson()) {
            abort(401, 'Unauthorized');
        }
    }
    public function handle($request, Closure $next)
{
    \Log::info("🔐 JWT middleware triggered", ['token' => $request->bearerToken()]);
    return parent::handle($request, $next);
}

}