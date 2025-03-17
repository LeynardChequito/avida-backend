<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\Traffic;

class TrackVisitor
{
    public function handle($request, Closure $next)
    {
        $referer = $request->header('referer') ?? '';

        if ($referer) {
            $source = parse_url($referer, PHP_URL_HOST);
        } else {
            $source = 'Direct';
        }

        // ✅ If the source exists, update count; otherwise, insert new record
        Traffic::updateOrInsert(
            ['source' => $source],
            ['visits' => \DB::raw('visits + 1')]
        );

        return $next($request);
    }
}
