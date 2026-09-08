<?php

namespace App\Http\Controllers;

use App\Models\Administration;
use App\Models\EmployeeMutation;
use App\Support\UserProject;
use Illuminate\Http\Request;

class EmployeeMutationController extends Controller
{
    public function store($employee_id, Request $request)
    {
        if ($r = UserProject::guardEmployeeId($request->employee_id ?? $employee_id)) {
            return $r;
        }

        $validated = $this->validatedMutation($request);

        $mutation = new EmployeeMutation;
        $mutation->employee_id = $validated['employee_id'];
        $mutation->project_id = $validated['project_id'];
        $mutation->mutated_at = $validated['mutated_at'];
        $mutation->status = $validated['status'];
        $mutation->remarks = $validated['remarks'] ?? null;
        $mutation->save();

        $this->syncActiveAdministrationProject($mutation->employee_id);

        return redirect('employees/'.$employee_id.'#mutations')
            ->with('toast_success', 'Mutation added successfully');
    }

    public function update(Request $request, $id)
    {
        $mutation = EmployeeMutation::findOrFail($id);
        if ($r = UserProject::guardEmployeeId($mutation->employee_id)) {
            return $r;
        }

        $validated = $this->validatedMutation($request);
        if ($r = UserProject::guardEmployeeId($validated['employee_id'])) {
            return $r;
        }

        $mutation->employee_id = $validated['employee_id'];
        $mutation->project_id = $validated['project_id'];
        $mutation->mutated_at = $validated['mutated_at'];
        $mutation->status = $validated['status'];
        $mutation->remarks = $validated['remarks'] ?? null;
        $mutation->save();

        $this->syncActiveAdministrationProject($mutation->employee_id);

        return redirect('employees/'.$mutation->employee_id.'#mutations')
            ->with('toast_success', 'Mutation updated successfully');
    }

    public function delete($employee_id, $id)
    {
        if ($r = UserProject::guardEmployeeId($employee_id)) {
            return $r;
        }

        $mutation = EmployeeMutation::findOrFail($id);
        if ((string) $mutation->employee_id !== (string) $employee_id) {
            return UserProject::redirectAccessDenied();
        }

        $mutation->delete();
        $this->syncActiveAdministrationProject($employee_id);

        return redirect('employees/'.$employee_id.'#mutations')
            ->with('toast_success', 'Mutation deleted successfully');
    }

    public function deleteAll($employee_id)
    {
        if ($r = UserProject::guardEmployeeId($employee_id)) {
            return $r;
        }

        EmployeeMutation::where('employee_id', $employee_id)->delete();

        return redirect('employees/'.$employee_id.'#mutations')
            ->with('toast_success', 'All mutations deleted successfully');
    }

    /**
     * @return array{employee_id: mixed, project_id: int, mutated_at: string, status: string, remarks: ?string}
     */
    private function validatedMutation(Request $request): array
    {
        return $request->validate([
            'employee_id' => 'required',
            'mutated_at' => 'required|date',
            'status' => 'required|in:active,inactive',
            'remarks' => 'nullable|string',
            'project_id' => [
                'required',
                'exists:projects,id',
                function ($attribute, $value, $fail) {
                    if (! UserProject::canAccessProjectId((int) $value)) {
                        $fail('The selected project is invalid.');
                    }
                },
            ],
        ]);
    }

    private function syncActiveAdministrationProject(string $employeeId): void
    {
        $latest = EmployeeMutation::where('employee_id', $employeeId)
            ->where('status', EmployeeMutation::STATUS_ACTIVE)
            ->orderByDesc('mutated_at')
            ->orderByDesc('id')
            ->first();

        if (! $latest) {
            return;
        }

        Administration::where('employee_id', $employeeId)
            ->where('is_active', 1)
            ->update(['project_id' => $latest->project_id]);
    }
}
