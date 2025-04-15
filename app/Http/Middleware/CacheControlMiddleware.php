<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CacheControlMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        // Skip cache control for specific routes
        if ($this->shouldSkipCacheControl($request)) {
            return $response;
        }

        // Set default cache control headers
        $headers = [
            'Cache-Control' => 'no-store, no-cache, must-revalidate, max-age=0',
            'Pragma' => 'no-cache',
            'Expires' => 'Sat, 01 Jan 2000 00:00:00 GMT'
        ];

        // For API requests
        if ($request->is('api/*')) {
            $response->headers->add($headers);
            return $response;
        }

        // For web requests
        if ($request->method() === 'GET') {
            $headers['Cache-Control'] = 'private, no-cache, must-revalidate';
            unset($headers['Expires']);
        }

        $response->headers->add($headers);
        return $response;
    }

    /**
     * Determine if cache control should be skipped for the request
     */
    protected function shouldSkipCacheControl(Request $request): bool
    {
        // Skip for static files
        if ($request->is('*.css') || $request->is('*.js') || $request->is('*.jpg') || $request->is('*.png')) {
            return true;
        }

        // Skip for specific routes
        $skipRoutes = [
            'login',
            'register',
            'password/*',
            'sanctum/csrf-cookie'
        ];

        foreach ($skipRoutes as $route) {
            if ($request->is($route)) {
                return true;
            }
        }

        return false;
    }
}
