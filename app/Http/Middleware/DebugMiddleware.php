<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class DebugMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Debug root route issues
        if ($request->is('/')) {
            Log::info('DebugMiddleware: Root route accessed', [
                'method' => $request->method(),
                'url' => $request->fullUrl(),
                'route' => $request->route() ? $request->route()->getName() : 'no route',
                'user_agent' => $request->userAgent(),
            ]);
        }

        return $next($request);
    }
}
