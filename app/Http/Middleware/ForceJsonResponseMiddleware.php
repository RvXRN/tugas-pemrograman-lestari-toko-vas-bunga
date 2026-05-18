<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ForceJsonResponseMiddleware
{
    /**
     * Force all API responses to be JSON.
     * Prevents HTML injection via Accept header manipulation and
     * ensures API never leaks HTML error pages to clients.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Override Accept header so Laravel always returns JSON
        $request->headers->set('Accept', 'application/json');

        return $next($request);
    }
}
