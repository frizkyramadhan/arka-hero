<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class UserDataFilter
{
    /**
     * Handle an incoming request.
     *
     * Apply data filtering for users with 'user' role to ensure they can only access their own data.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        // Only apply filtering for users with 'user' role
        if ($user && $user->hasRole('user')) {
            // Add ownership filters to request for controllers to use
            $request->merge([
                'user_filters' => [
                    'employee_id' => $user->employee_id,
                    'administration_id' => $user->administration_id,
                    'user_id' => $user->id,
                ]
            ]);

            // Validate route parameters if they contain IDs that should be owned by user
            if (!$this->validateRouteOwnership($request, $user)) {
                abort(403, 'You do not have permission to access this resource.');
            }
        }

        return $next($request);
    }

    /**
     * Validate that route parameters belong to the authenticated user
     */
    private function validateRouteOwnership(Request $request, $user): bool
    {
        $route = $request->route();
        if (!$route) {
            return true; // No route, allow
        }

        $parameters = $route->parameters();

        // Check different route parameter patterns
        foreach ($parameters as $key => $value) {
            // For leave request routes
            if (str_contains($key, 'leave_request') || str_contains($key, 'leaveRequest')) {
                $leaveRequest = \App\Models\LeaveRequest::find($value);
                if ($leaveRequest && $leaveRequest->employee_id !== $user->employee_id) {
                    return false;
                }
            }

            // For official travel routes
            if (str_contains($key, 'official_travel') || str_contains($key, 'officialtravel')) {
                $officialTravel = \App\Models\Officialtravel::find($value);
                if ($officialTravel) {
                    $isTraveler = $officialTravel->traveler_id === $user->administration_id;
                    $isFollower = $officialTravel->details()
                        ->where('follower_id', $user->administration_id)
                        ->exists();

                    if (!$isTraveler && !$isFollower) {
                        return false;
                    }
                }
            }

            // For recruitment request routes
            if (str_contains($key, 'recruitment_request') || str_contains($key, 'recruitmentRequest')) {
                $recruitmentRequest = \App\Models\RecruitmentRequest::find($value);
                if ($recruitmentRequest && $recruitmentRequest->created_by !== $user->id) {
                    return false;
                }
            }
        }

        return true;
    }
}
