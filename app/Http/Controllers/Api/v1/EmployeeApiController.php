<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\Administration;
use App\Http\Resources\AdministrationResource;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class EmployeeApiController extends Controller
{
    /**
     * Display a listing of all administrations with their relationships.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        try {
            $administrations = Administration::with([
                'employee',
                'employee.religion',
                'position',
                'position.department',
                'project'
            ])->get();

            if ($administrations->isEmpty()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'No administrations found',
                    'data' => []
                ], 404);
            }

            return response()->json([
                'status' => 'success',
                'data' => AdministrationResource::collection($administrations)
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display a listing of active administrations.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function activeEmployees()
    {
        try {
            $administrations = Administration::where('is_active', 1)
                ->with([
                    'employee',
                    'employee.religion',
                    'position',
                    'position.department',
                    'project'
                ])->get();

            if ($administrations->isEmpty()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'No active administrations found',
                    'data' => []
                ], 404);
            }

            return response()->json([
                'status' => 'success',
                'data' => AdministrationResource::collection($administrations)
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get simple employee list for dropdowns
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getEmployees()
    {
        try {
            $employees = Employee::select('id', 'fullname')
                ->whereHas('administrations', function ($query) {
                    $query->where('is_active', 1);
                })
                ->orderBy('fullname')
                ->get();

            return response()->json($employees);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display all administrations for an employee.
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        try {
            // Here we interpret $id as employee_id
            $administrations = Administration::where('employee_id', $id)
                ->with([
                    'employee',
                    'employee.religion',
                    'position',
                    'position.department',
                    'project'
                ])->get();

            if ($administrations->isEmpty()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'No administrations found for this employee',
                    'data' => []
                ], 404);
            }

            // Check if employee has at least one active administration
            $hasActiveAdmin = $administrations->where('is_active', 1)->count() > 0;
            if (!$hasActiveAdmin) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Employee does not have an active administration record',
                    'data' => []
                ], 404);
            }

            return response()->json([
                'status' => 'success',
                'data' => AdministrationResource::collection($administrations)
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Search for administrations based on various criteria.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function search(Request $request)
    {
        try {
            $query = Administration::query();
            $hasFilters = false;

            // NIK filter
            if ($request->has('nik')) {
                $hasFilters = true;
                $query->where('nik', 'LIKE', '%' . $request->nik . '%');
            }

            // Position filter
            if ($request->has('position')) {
                $hasFilters = true;
                $query->whereHas('position', function (Builder $q) use ($request) {
                    $q->where('position_name', 'LIKE', '%' . $request->position . '%');
                });
            }

            // Department filter
            if ($request->has('department')) {
                $hasFilters = true;
                $query->whereHas('position.department', function (Builder $q) use ($request) {
                    $q->where('department_name', 'LIKE', '%' . $request->department . '%');
                });
            }

            // Project filter
            if ($request->has('project')) {
                $hasFilters = true;
                $query->whereHas('project', function (Builder $q) use ($request) {
                    $q->where('project_code', 'LIKE', '%' . $request->project . '%')
                        ->orWhere('project_name', 'LIKE', '%' . $request->project . '%');
                });
            }

            // Employee name filter
            if ($request->has('name')) {
                $hasFilters = true;
                $query->whereHas('employee', function (Builder $q) use ($request) {
                    $q->where('fullname', 'LIKE', '%' . $request->name . '%');
                });
            }

            // If no filters, default to active administrations
            if (!$hasFilters) {
                $query->where('is_active', 1);
            }

            // Get administrations with all related data
            $administrations = $query->with([
                'employee',
                'employee.religion',
                'position',
                'position.department',
                'project'
            ])->get();

            if ($administrations->isEmpty()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'No administrations found matching your criteria',
                    'data' => []
                ], 404);
            }

            return response()->json([
                'status' => 'success',
                'data' => AdministrationResource::collection($administrations)
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
