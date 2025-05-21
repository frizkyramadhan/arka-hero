<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Models\Officialtravel;
use App\Enums\ClaimStatus;
use App\Http\Resources\OfficialtravelResource;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class OfficialtravelApiController extends Controller
{
    /**
     * Search official travels
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function search(Request $request)
    {
        try {
            $query = Officialtravel::query();
            $hasFilters = false;

            // Filter by travel number
            if ($request->filled('travel_number')) {
                $hasFilters = true;
                $query->where('official_travel_number', 'LIKE', '%' . $request->travel_number . '%');
            }

            // Filter by traveler
            if ($request->filled('traveler')) {
                $hasFilters = true;
                $query->whereHas('traveler.employee', function (Builder $q) use ($request) {
                    $q->where('fullname', 'LIKE', '%' . $request->traveler . '%');
                });
            }

            // Filter by department
            if ($request->filled('department')) {
                $hasFilters = true;
                $query->whereHas('traveler.position.department', function (Builder $q) use ($request) {
                    $q->where('department_name', 'LIKE', '%' . $request->department . '%');
                });
            }

            // Filter by project
            if ($request->filled('project')) {
                $hasFilters = true;
                $query->whereHas('project', function (Builder $q) use ($request) {
                    $q->where('project_code', 'LIKE', '%' . $request->project . '%')
                        ->orWhere('project_name', 'LIKE', '%' . $request->project . '%');
                });
            }

            // If no filters provided or all filter values are empty/null, return error
            if (!$hasFilters) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'At least one filter parameter with a non-empty value is required. Available filters: travel_number, traveler, department, project',
                    'data' => []
                ], 400);
            }

            // Get official travels with all related data
            $officialtravels = $query->with([
                'traveler.employee',
                'traveler.position.department',
                'traveler.project',
                'project',
                'transportation',
                'accommodation',
                'details.follower.employee',
                'details.follower.position.department',
                'details.follower.project',
                'arrivalChecker',
                'departureChecker',
                'recommender',
                'approver',
                'creator'
            ])
                ->whereNot('official_travel_status', 'draft')
                ->where('is_claimed', 'no')
                ->orderBy('created_at', 'desc')->get();

            if ($officialtravels->isEmpty()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'No official travels found matching your criteria',
                    'data' => []
                ], 404);
            }

            return response()->json([
                'status' => 'success',
                'data' => OfficialtravelResource::collection($officialtravels)
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function search_claimed(Request $request)
    {
        try {
            $query = Officialtravel::query();
            $hasFilters = false;

            // Filter by travel number
            if ($request->filled('travel_number')) {
                $hasFilters = true;
                $query->where('official_travel_number', 'LIKE', '%' . $request->travel_number . '%');
            }

            // Filter by traveler
            if ($request->filled('traveler')) {
                $hasFilters = true;
                $query->whereHas('traveler.employee', function (Builder $q) use ($request) {
                    $q->where('fullname', 'LIKE', '%' . $request->traveler . '%');
                });
            }

            // Filter by department
            if ($request->filled('department')) {
                $hasFilters = true;
                $query->whereHas('traveler.position.department', function (Builder $q) use ($request) {
                    $q->where('department_name', 'LIKE', '%' . $request->department . '%');
                });
            }

            // Filter by project
            if ($request->filled('project')) {
                $hasFilters = true;
                $query->whereHas('project', function (Builder $q) use ($request) {
                    $q->where('project_code', 'LIKE', '%' . $request->project . '%')
                        ->orWhere('project_name', 'LIKE', '%' . $request->project . '%');
                });
            }

            // If no filters provided or all filter values are empty/null, return error
            if (!$hasFilters) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'At least one filter parameter with a non-empty value is required. Available filters: travel_number, traveler, department, project',
                    'data' => []
                ], 400);
            }

            // Get official travels with all related data
            $officialtravels = $query->with([
                'traveler.employee',
                'traveler.position.department',
                'traveler.project',
                'project',
                'transportation',
                'accommodation',
                'details.follower.employee',
                'details.follower.position.department',
                'details.follower.project',
                'arrivalChecker',
                'departureChecker',
                'recommender',
                'approver',
                'creator'
            ])
                ->whereNotNull('departure_from_destination')
                ->where('is_claimed', 'no')
                ->orderBy('created_at', 'desc')
                ->get();

            if ($officialtravels->isEmpty()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'No official travels found matching your criteria',
                    'data' => []
                ], 404);
            }

            return response()->json([
                'status' => 'success',
                'data' => OfficialtravelResource::collection($officialtravels)
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get official travel details
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        try {
            $query = Officialtravel::query();

            $officialtravel = $query->with([
                'traveler.employee',
                'traveler.position.department',
                'traveler.project',
                'project',
                'transportation',
                'accommodation',
                'details.follower.employee',
                'details.follower.position.department',
                'details.follower.project',
                'arrivalChecker',
                'departureChecker',
                'recommender',
                'approver',
                'creator'
            ])->find($id);

            if (!$officialtravel) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Official travel not found',
                    'data' => []
                ], 404);
            }

            return response()->json([
                'status' => 'success',
                'data' => new OfficialtravelResource($officialtravel)
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update official travel claim status
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateClaim(Request $request)
    {
        try {
            $this->validate($request, [
                'official_travel_number' => 'required|string',
                'is_claimed' => 'required|in:yes,no'
            ]);

            DB::beginTransaction();

            $query = Officialtravel::query();

            $officialtravel = $query->with([
                'traveler.employee',
                'traveler.position.department',
                'traveler.project',
                'project',
                'transportation',
                'accommodation',
                'details.follower.employee',
                'details.follower.position.department',
                'details.follower.project',
                'arrivalChecker',
                'departureChecker',
                'recommender',
                'approver',
                'creator'
            ])->where('official_travel_number', $request->official_travel_number)->first();

            if (!$officialtravel) {
                DB::rollBack();
                return response()->json([
                    'status' => 'error',
                    'message' => 'Official travel not found',
                    'data' => []
                ], 404);
            }

            // Check if is_claimed is already yes
            if ($officialtravel->is_claimed === ClaimStatus::YES->value) {
                DB::rollBack();
                return response()->json([
                    'status' => 'error',
                    'message' => 'This official travel has already been claimed',
                    'data' => []
                ], 400);
            }

            $officialtravel->update([
                'is_claimed' => $request->is_claimed,
                'claimed_at' => now()
            ]);

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Claim status updated successfully',
                'data' => new OfficialtravelResource($officialtravel)
            ], 200);
        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Validation error',
                'errors' => $e->getMessage()
            ], 422);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
