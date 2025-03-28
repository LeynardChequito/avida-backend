<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class Cors
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // ✅ Set CORS Headers Correctly
        $response->headers->set("Access-Control-Allow-Origin", "http://localhost:3000");
        $response->headers->set("Access-Control-Allow-Methods", "GET, POST, PUT, DELETE, OPTIONS");
        $response->headers->set("Access-Control-Allow-Headers", "Origin, Content-Type, Authorization, X-Requested-With");

        // ✅ Allow Preflight Requests
        if ($request->isMethod("OPTIONS")) {
            return response()->json(["message" => "CORS preflight OK"], 200, $response->headers->all());
        }

        return $response;
    }
}
