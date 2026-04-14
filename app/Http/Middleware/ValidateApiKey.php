<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ValidateApiKey
{
    /**
     * Require a valid API key via X-API-Key header or Authorization: Bearer token.
     *
     * Set API_KEY in .env. Use API_REQUIRE_KEY=false locally to bypass (never in production).
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! config('services.api.require_key', true)) {
            return $next($request);
        }

        $configured = config('services.api.key');
        if (! is_string($configured) || $configured === '') {
            return response()->json([
                'message' => 'API key is not configured on the server.',
            ], 503);
        }

        $provided = $request->header('X-API-Key') ?? $request->bearerToken();

        if (! is_string($provided) || ! hash_equals($configured, $provided)) {
            return response()->json([
                'message' => 'Invalid or missing API key.',
            ], 401);
        }

        return $next($request);
    }
}
