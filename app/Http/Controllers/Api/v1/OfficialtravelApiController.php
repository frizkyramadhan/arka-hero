<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Models\Officialtravel;
use App\Enums\ClaimStatus;
use App\Http\Resources\OfficialtravelResource;
use Illuminate\Http\Request;
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
            $query = Officialtravel::with([
                'traveler.employee',
                'project',
                'transportation',
                'accommodation',
                'details.follower.employee',
                'recommender',
                'approver'
            ]);

            // Filter by travel number
            if ($request->has('travel_number')) {
                $query->where('official_travel_number', 'LIKE', '%' . $request->travel_number . '%');
            }

            // Filter by traveler
            if ($request->has('traveler')) {
                $query->whereHas('traveler.employee', function ($q) use ($request) {
                    $q->where('fullname', 'LIKE', '%' . $request->traveler . '%');
                });
            }

            // Filter by department
            if ($request->has('department')) {
                $query->whereHas('traveler.position.department', function ($q) use ($request) {
                    $q->where('department_name', 'LIKE', '%' . $request->department . '%');
                });
            }

            $officialtravels = $query
                ->where('approval_status', 'approved')
                ->where('arrival_at_destination', null)
                ->where('official_travel_status', 'open')
                ->orderBy('created_at', 'desc')
                ->get();

            if ($officialtravels->isEmpty()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'No official travels found',
                    'data' => []
                ], 404);
            } else {
                return response()->json([
                    'status' => 'success',
                    'data' => OfficialtravelResource::collection($officialtravels)
                ], 200);
            }
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
            $officialtravel = Officialtravel::with([
                'traveler.employee',
                'project',
                'transportation',
                'accommodation',
                'details.follower.employee',
                'recommender',
                'approver'
            ])->findOrFail($id);

            return response()->json([
                'status' => 'success',
                'data' => new OfficialtravelResource($officialtravel)
            ], 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Official travel not found'
            ], 404);
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
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateClaim(Request $request, $id)
    {
        try {
            $this->validate($request, [
                'is_claimed' => 'required|in:yes,no'
            ]);

            DB::beginTransaction();

            $officialtravel = Officialtravel::with([
                'traveler.employee',
                'project',
                'transportation',
                'accommodation',
                'details.follower.employee',
                'recommender',
                'approver'
            ])->find($id);

            if (!$officialtravel) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Official travel not found'
                ], 404);
            }

            // Check if departure_from_destination is not null
            if (is_null($officialtravel->departure_from_destination)) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Cannot update claim status because departure at destination is not recorded'
                ], 400);
            }

            // Check if is_claimed is already yes
            if ($officialtravel->is_claimed === ClaimStatus::YES->value) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'This official travel has already been claimed'
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
                'errors' => $e->errors()
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
