<?php

namespace App\Traits;

use Illuminate\Support\Facades\Auth;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;

trait RecruitmentPermissions
{
    /**
     * Check if current user has permission
     *
     * @param string $permission
     * @return bool
     */
    protected function hasPermission(string $permission): bool
    {
        if (!Auth::check()) {
            return false;
        }

        return Auth::user()->can($permission);
    }

    /**
     * Check if current user has ALL specified permissions
     *
     * @param array $permissions
     * @return bool
     */
    protected function hasAllPermissions(array $permissions): bool
    {
        if (!Auth::check()) {
            return false;
        }

        $user = Auth::user();

        foreach ($permissions as $permission) {
            if (!$user->can($permission)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Check if current user has ANY of the specified permissions
     *
     * @param array $permissions
     * @return bool
     */
    protected function hasAnyPermission(array $permissions): bool
    {
        if (!Auth::check()) {
            return false;
        }

        $user = Auth::user();

        foreach ($permissions as $permission) {
            if ($user->can($permission)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if current user has role
     *
     * @param string $role
     * @return bool
     */
    protected function hasRole(string $role): bool
    {
        if (!Auth::check()) {
            return false;
        }

        return Auth::user()->hasRole($role);
    }

    /**
     * Check if current user has ANY of the specified roles
     *
     * @param array $roles
     * @return bool
     */
    protected function hasAnyRole(array $roles): bool
    {
        if (!Auth::check()) {
            return false;
        }

        return Auth::user()->hasAnyRole($roles);
    }

    /**
     * Abort with 403 if user doesn't have permission
     *
     * @param string $permission
     * @param string|null $message
     * @return void
     */
    protected function authorize(string $permission, string $message = null): void
    {
        if (!$this->hasPermission($permission)) {
            $message = $message ?? 'You do not have permission to access this resource.';
            abort(403, $message);
        }
    }

    /**
     * Authorize multiple permissions (user must have ALL)
     *
     * @param array $permissions
     * @param string|null $message
     * @return void
     */
    protected function authorizeAll(array $permissions, string $message = null): void
    {
        if (!$this->hasAllPermissions($permissions)) {
            $message = $message ?? 'You do not have all required permissions.';
            abort(403, $message);
        }
    }

    /**
     * Authorize any permission (user must have at least ONE)
     *
     * @param array $permissions
     * @param string|null $message
     * @return void
     */
    protected function authorizeAny(array $permissions, string $message = null): void
    {
        if (!$this->hasAnyPermission($permissions)) {
            $message = $message ?? 'You do not have any of the required permissions.';
            abort(403, $message);
        }
    }

    /**
     * Return JSON response for permission denied
     *
     * @param string $permission
     * @param string|null $message
     * @return JsonResponse
     */
    protected function permissionDeniedJson(string $permission, string $message = null): JsonResponse
    {
        return response()->json([
            'error' => 'Forbidden',
            'message' => $message ?? 'You do not have permission to access this resource.',
            'required_permission' => $permission
        ], 403);
    }

    /**
     * Return redirect response for permission denied
     *
     * @param string|null $message
     * @return RedirectResponse
     */
    protected function permissionDeniedRedirect(string $message = null): RedirectResponse
    {
        $message = $message ?? 'You do not have permission to access this resource.';
        return redirect()->back()->with('error', $message);
    }

    /**
     * Check stage-specific permissions
     *
     * @param string $stage
     * @param string $action
     * @return bool
     */
    protected function canAccessStage(string $stage, string $action = 'view'): bool
    {
        // Build permission name based on stage and action
        $permission = "recruitment_{$stage}.{$action}";

        return $this->hasPermission($permission);
    }

    /**
     * Check if user can advance session to next stage
     *
     * @param string $currentStage
     * @param string $nextStage
     * @return bool
     */
    protected function canAdvanceStage(string $currentStage, string $nextStage): bool
    {
        // Check if user can complete current stage
        if (!$this->canAccessStage($currentStage, 'conduct')) {
            return false;
        }

        // Check if user can advance sessions
        if (!$this->hasPermission('recruitment_sessions.advance_stage')) {
            return false;
        }

        return true;
    }

    /**
     * Check if user can conduct specific assessment
     *
     * @param string $assessmentType
     * @return bool
     */
    protected function canConductAssessment(string $assessmentType): bool
    {
        return $this->canAccessStage($assessmentType, 'conduct');
    }

    /**
     * Check if user can schedule specific assessment
     *
     * @param string $assessmentType
     * @return bool
     */
    protected function canScheduleAssessment(string $assessmentType): bool
    {
        return $this->canAccessStage($assessmentType, 'schedule');
    }

    /**
     * Check if user can score specific assessment
     *
     * @param string $assessmentType
     * @return bool
     */
    protected function canScoreAssessment(string $assessmentType): bool
    {
        return $this->canAccessStage($assessmentType, 'score');
    }

    /**
     * Get user's recruitment role level
     *
     * @return string
     */
    protected function getRecruitmentRoleLevel(): string
    {
        if (!Auth::check()) {
            return 'guest';
        }

        $user = Auth::user();

        if ($user->hasRole('HR Manager')) {
            return 'manager';
        }

        if ($user->hasRole('HR Staff')) {
            return 'staff';
        }

        if ($user->hasRole('Department Head')) {
            return 'department_head';
        }

        if ($user->hasRole('Interviewer')) {
            return 'interviewer';
        }

        if ($user->hasRole('Assessor')) {
            return 'assessor';
        }

        if ($user->hasRole('Medical Officer')) {
            return 'medical_officer';
        }

        if ($user->hasRole('Recruitment Admin')) {
            return 'admin';
        }

        return 'user';
    }

    /**
     * Check if user is HR Manager or higher
     *
     * @return bool
     */
    protected function isHRManager(): bool
    {
        return $this->hasRole('HR Manager');
    }

    /**
     * Check if user is HR Staff or higher
     *
     * @return bool
     */
    protected function isHRStaff(): bool
    {
        return $this->hasAnyRole(['HR Manager', 'HR Staff']);
    }

    /**
     * Get permissions for current user (filtered by recruitment)
     *
     * @return array
     */
    protected function getRecruitmentPermissions(): array
    {
        if (!Auth::check()) {
            return [];
        }

        return Auth::user()->getAllPermissions()
            ->filter(function ($permission) {
                return str_starts_with($permission->name, 'recruitment_');
            })
            ->pluck('name')
            ->toArray();
    }
}
