<?php

namespace App\Services;

use App\Models\Administration;
use App\Models\Employee;
use App\Models\RecruitmentHiring;
use App\Models\RecruitmentSession;
use Illuminate\Support\Facades\Auth;

class RecruitmentHireEmployeeService
{
    public function registerFromHireData(
        RecruitmentSession $session,
        array $employeeData,
        array $adminData,
        string $agreementType
    ): Employee {
        $session->loadMissing(['candidate', 'fptk', 'mppDetail.mpp']);

        $candidate = $session->candidate;

        $employeePayload = [
            'fullname' => $employeeData['fullname'] ?? ($candidate->fullname ?? ''),
            'emp_pob' => $employeeData['emp_pob'] ?? '-',
            'emp_dob' => $employeeData['emp_dob'] ?? now()->toDateString(),
            'blood_type' => $employeeData['blood_type'] ?? null,
            'religion_id' => $employeeData['religion_id'] ?? null,
            'nationality' => $employeeData['nationality'] ?? null,
            'gender' => $employeeData['gender'] ?? null,
            'marital' => $employeeData['marital'] ?? null,
            'address' => $employeeData['address'] ?? $candidate->address ?? null,
            'village' => $employeeData['village'] ?? null,
            'ward' => $employeeData['ward'] ?? null,
            'district' => $employeeData['district'] ?? null,
            'city' => $employeeData['city'] ?? null,
            'phone' => $employeeData['phone'] ?? ($candidate->phone ?? null),
            'email' => $employeeData['email'] ?? ($candidate->email ?? null),
            'identity_card' => $employeeData['identity_card'],
            'user_id' => Auth::id(),
        ];

        $employee = Employee::where('identity_card', $employeePayload['identity_card'])->first();
        if ($employee) {
            $employee->update($employeePayload);
        } else {
            $employee = Employee::create($employeePayload);
        }

        $this->deactivateActiveAdministrations($employee->id);
        $this->createAdministrationForHire($session, $employee, $adminData, $agreementType);

        return $employee;
    }

    public function linkExistingEmployeeFromHire(string $employeeId): Employee
    {
        return Employee::findOrFail($employeeId);
    }

    public function markHiringEmployeeRegistered(RecruitmentHiring $hiring, Employee $employee): void
    {
        $hiring->update([
            'employee_id' => $employee->id,
            'employee_registered_at' => now(),
            'employee_registered_by' => Auth::id(),
        ]);
    }

    private function deactivateActiveAdministrations(string $employeeId): void
    {
        Administration::where('employee_id', $employeeId)->where('is_active', 1)->update(['is_active' => 0]);
    }

    private function createAdministrationForHire(
        RecruitmentSession $session,
        Employee $employee,
        array $adminData,
        string $agreementType
    ): Administration {
        $positionId = $adminData['position_id'] ?? null;
        if (! $positionId) {
            if ($session->fptk_id && $session->fptk) {
                $positionId = $session->fptk->position_id ?? null;
            } elseif ($session->mpp_detail_id && $session->mppDetail) {
                $positionId = $session->mppDetail->position_id ?? null;
            }
        }

        $projectId = $adminData['project_id'] ?? null;
        if (! $projectId) {
            if ($session->fptk_id && $session->fptk) {
                $projectId = $session->fptk->project_id ?? null;
            } elseif ($session->mpp_detail_id && $session->mppDetail && $session->mppDetail->mpp) {
                $projectId = $session->mppDetail->mpp->project_id ?? null;
            }
        }

        $requestNumber = $adminData['no_fptk'] ?? null;
        if (! $requestNumber) {
            if ($session->fptk_id && $session->fptk) {
                $requestNumber = $session->fptk->request_number ?? null;
            } elseif ($session->mpp_detail_id && $session->mppDetail && $session->mppDetail->mpp) {
                $requestNumber = $session->mppDetail->mpp->mpp_number ?? null;
            }
        }

        return Administration::create([
            'employee_id' => $employee->id,
            'project_id' => $projectId,
            'position_id' => $positionId,
            'grade_id' => $adminData['grade_id'] ?? null,
            'level_id' => $adminData['level_id'] ?? null,
            'nik' => $adminData['nik'],
            'class' => $adminData['class'],
            'doh' => $adminData['doh'],
            'poh' => $adminData['poh'],
            'foc' => $agreementType === 'pkwt' ? ($adminData['foc'] ?? null) : null,
            'agreement' => $adminData['agreement'] ?? strtoupper($agreementType),
            'no_fptk' => $requestNumber,
            'is_active' => 1,
            'user_id' => Auth::id(),
        ]);
    }
}
