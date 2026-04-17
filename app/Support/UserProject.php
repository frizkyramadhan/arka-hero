<?php

namespace App\Support;

use App\Models\Employee;
use App\Models\Project;
use App\Models\RecruitmentCandidate;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;

/**
 * User assignment via `user_project` pivot.
 *
 * Public API (intentionally small):
 * - {@see projectsForSelect} — dropdown proyek sesuai assignment user.
 * - {@see employeesForSelect} — dropdown karyawan sesuai assignment (mode `linked` atau `active_administration`).
 * - {@see scopeToAssignedProjects} — filter query yang punya kolom `project_id` (list data per proyek).
 * - Helpers tambahan untuk bentuk query khusus (employee FK, recruitment session/candidate, join administrasi).
 */
final class UserProject
{
    public const EMPLOYEE_SELECT_LINKED = 'linked';

    public const EMPLOYEE_SELECT_ACTIVE_ADMINISTRATION = 'active_administration';

    /**
     * Narrow guard user to {@see User} so role/relationship methods are defined for static analysis.
     */
    private static function resolveUser(?User $user = null): ?User
    {
        if ($user !== null) {
            return $user;
        }

        $auth = auth()->user();

        return $auth instanceof User ? $auth : null;
    }

    /**
     * null = no pivot restriction (administrator).
     * array (possibly empty) = only these project IDs (non-admin).
     */
    public static function assignmentScope(?User $user = null): ?array
    {
        $user = self::resolveUser($user);
        if ($user === null) {
            return [];
        }

        return $user->projects()->pluck('projects.id')->all();
    }

    public static function canAccessProjectId(int $projectId, ?User $user = null): bool
    {
        $user = self::resolveUser($user);
        if ($user === null) {
            return false;
        }

        $scope = self::assignmentScope($user);

        return is_array($scope) && in_array($projectId, $scope, true);
    }

    /**
     * Query untuk dropdown proyek (default: proyek aktif saja).
     */
    public static function projectsQuery(?User $user = null, bool $onlyActive = true): Builder
    {
        $user = self::resolveUser($user);
        $q = Project::query()->orderBy('project_code');
        if ($onlyActive) {
            $q->where('project_status', 1);
        }

        $scope = self::assignmentScope($user);
        if ($scope !== null) {
            if ($scope === []) {
                $q->whereRaw('0 = 1');
            } else {
                $q->whereIn('id', $scope);
            }
        }

        return $q;
    }

    /**
     * @return \Illuminate\Support\Collection<int, Project>
     */
    public static function projectsForSelect(?User $user = null, bool $onlyActive = true): \Illuminate\Support\Collection
    {
        return self::projectsQuery($user, $onlyActive)->get();
    }

    /**
     * Dropdown karyawan sesuai `user_project`.
     *
     * - {@see EMPLOYEE_SELECT_LINKED}: karyawan yang punya minimal satu baris administrasi di proyek assignment (distinct).
     * - {@see EMPLOYEE_SELECT_ACTIVE_ADMINISTRATION}: join ke administrasi aktif, menyertakan `nik` (urut `fullname` atau `nik`).
     *
     * @param  'fullname'|'nik'  $orderBy  Hanya dipakai jika mode `active_administration`.
     * @return \Illuminate\Support\Collection<int, Employee>
     */
    public static function employeesForSelect(?User $user = null, string $mode = self::EMPLOYEE_SELECT_LINKED, string $orderBy = 'fullname'): \Illuminate\Support\Collection
    {
        if ($mode === self::EMPLOYEE_SELECT_ACTIVE_ADMINISTRATION) {
            $q = Employee::query()
                ->join('administrations', 'employees.id', '=', 'administrations.employee_id')
                ->where('administrations.is_active', 1)
                ->select('employees.*', 'administrations.nik');

            if ($orderBy === 'nik') {
                $q->orderBy('administrations.nik', 'asc');
            } else {
                $q->orderBy('employees.fullname', 'asc');
            }

            $scope = self::assignmentScope($user);
            if ($scope !== null) {
                if ($scope === []) {
                    return collect();
                }
                $q->whereIn('administrations.project_id', $scope);
            }

            return $q->get();
        }

        $q = Employee::query()->orderBy('fullname');
        self::scopeQueryToEmployeesLinkedViaAdministrations($q, 'employees.id');

        return $q->distinct()->get();
    }

    public static function canViewEmployee(Employee $employee, ?User $user = null): bool
    {
        $user = self::resolveUser($user);
        if ($user === null) {
            return false;
        }

        $scope = self::assignmentScope($user);
        if (! is_array($scope) || $scope === []) {
            return false;
        }

        if (! $employee->administrations()->exists()) {
            return true;
        }

        return $employee->administrations()->whereIn('project_id', $scope)->exists();
    }

    /**
     * Listing karyawan (left join administrasi): baris tanpa administrasi tetap tampil jika perlu onboarding.
     */
    public static function scopeEmployeeListQueryToAssignedProjects(Builder $query): Builder
    {
        $scope = self::assignmentScope();
        if ($scope === null) {
            return $query;
        }
        if ($scope === []) {
            return $query->whereRaw('0 = 1');
        }

        return $query->where(function ($w) use ($scope) {
            $w->whereIn('administrations.project_id', $scope)
                ->orWhereNull('administrations.id');
        });
    }

    /**
     * Query yang sudah join `administrations` — batasi ke proyek assignment.
     */
    public static function scopeAdministrationJoinToAssignedProjects(Builder $query): Builder
    {
        $scope = self::assignmentScope();
        if ($scope === null) {
            return $query;
        }
        if ($scope === []) {
            return $query->whereRaw('0 = 1');
        }

        return $query->whereIn('administrations.project_id', $scope);
    }

    /**
     * Filter baris berdasarkan `employee_id` yang punya administrasi di proyek assignment (subquery).
     *
     * @param  \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder  $query
     */
    public static function scopeQueryToEmployeesLinkedViaAdministrations($query, string $employeeIdColumn): void
    {
        $scope = self::assignmentScope();
        if ($scope === null) {
            return;
        }
        if ($scope === []) {
            $query->whereRaw('0 = 1');

            return;
        }

        $query->whereIn($employeeIdColumn, function ($sub) use ($scope) {
            $sub->select('administrations.employee_id')
                ->from('administrations')
                ->whereIn('administrations.project_id', $scope);
        });
    }

    /**
     * Filter query yang punya kolom `project_id` (FPTK, MPP, dll.) ke proyek assignment user.
     *
     * @param  \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder
     */
    public static function scopeToAssignedProjects($query, string $projectIdColumn = 'project_id')
    {
        $scope = self::assignmentScope();
        if ($scope === null) {
            return $query;
        }
        if ($scope === []) {
            return $query->whereRaw('0 = 1');
        }

        return $query->whereIn($projectIdColumn, $scope);
    }

    /**
     * `recruitment_sessions`: sumber FPTK atau MPP harus di proyek assignment.
     *
     * @param  \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder
     */
    public static function scopeRecruitmentSessionsToAssignedProjects($query)
    {
        $scope = self::assignmentScope();
        if ($scope === null) {
            return $query;
        }
        if ($scope === []) {
            return $query->whereRaw('0 = 1');
        }

        return $query->where(function ($w) use ($scope) {
            $w->whereHas('fptk', fn ($q) => $q->whereIn('project_id', $scope))
                ->orWhereHas('mppDetail.mpp', fn ($q) => $q->whereIn('project_id', $scope));
        });
    }

    /**
     * `recruitment_candidates`: pool global (tanpa session) atau punya session ke FPTK/MPP di proyek assignment.
     *
     * @param  \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder
     */
    public static function scopeRecruitmentCandidatesToAssignedProjects($query)
    {
        $scope = self::assignmentScope();
        if ($scope === null) {
            return $query;
        }
        if ($scope === []) {
            return $query->whereRaw('0 = 1');
        }

        return $query->where(function ($w) use ($scope) {
            $w->whereDoesntHave('sessions')
                ->orWhereHas('sessions', function ($sq) use ($scope) {
                    $sq->where(function ($w2) use ($scope) {
                        $w2->whereHas('fptk', fn ($q) => $q->whereIn('project_id', $scope))
                            ->orWhereHas('mppDetail.mpp', fn ($q) => $q->whereIn('project_id', $scope));
                    });
                });
        });
    }

    public static function canViewRecruitmentCandidate(RecruitmentCandidate $candidate, ?User $user = null): bool
    {
        $scope = self::assignmentScope($user);
        if ($scope === null) {
            return true;
        }
        if ($scope === []) {
            return false;
        }

        if (! $candidate->sessions()->exists()) {
            return true;
        }

        return $candidate->sessions()->where(function ($q) use ($scope) {
            $q->whereHas('fptk', fn ($q2) => $q2->whereIn('project_id', $scope))
                ->orWhereHas('mppDetail.mpp', fn ($q2) => $q2->whereIn('project_id', $scope));
        })->exists();
    }

    /**
     * Pastikan `project_id` ada di assignment user (bukan untuk unrestricted / null scope).
     */
    public static function guardProjectInAssignmentScope(int $projectId): ?RedirectResponse
    {
        $scope = self::assignmentScope();
        if ($scope === null) {
            return null;
        }
        if ($scope === [] || ! in_array((int) $projectId, array_map('intval', $scope), true)) {
            return self::redirectAccessDenied();
        }

        return null;
    }

    public static function guardEmployeeId(int|string $employeeId): ?RedirectResponse
    {
        $employee = Employee::findOrFail($employeeId);
        if (! self::canViewEmployee($employee)) {
            return self::redirectAccessDenied();
        }

        return null;
    }

    /**
     * Redirect with SweetAlert flash (session keys used by layouts/partials/scripts.blade.php).
     *
     * @param  string|null  $url  Optional explicit URL (overrides back).
     */
    public static function redirectAccessDenied(?string $url = null): RedirectResponse
    {
        $flash = [
            'toast_error' => 'Anda tidak memiliki akses ke data tersebut. Hubungi HO Balikpapan untuk bantuan.',
            'alert_title' => 'Akses ditolak',
            'alert_type' => 'warning',
        ];

        if ($url !== null) {
            return redirect()->to($url)->with($flash);
        }

        return redirect()->back(302, [], route('employees.index'))->with($flash);
    }
}
