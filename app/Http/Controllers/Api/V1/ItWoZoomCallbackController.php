<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\RoomConsumptionRequest;
use App\Services\ItWoZoomClient;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Callback from IT WO / rest-server when Zoom Meeting ID is filled.
 * Preferred sync path; HERO also supports polling GET on rest-server.
 */
class ItWoZoomCallbackController extends Controller
{
    public function __invoke(Request $request, ItWoZoomClient $client): JsonResponse
    {
        $apiKey = (string) config('it_wo.api_key');
        $incoming = $request->header('X-API-Key')
            ?: $request->header('arka-key')
            ?: $request->bearerToken();

        if ($apiKey !== '' && ! hash_equals($apiKey, (string) $incoming)) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 401);
        }

        $data = $request->validate([
            'source_document_id' => 'nullable|uuid',
            'it_wo_id' => 'nullable',
            'it_wo_number' => 'nullable|string|max:100',
            'status' => 'nullable|string|max:50',
            'zoom_meeting_id' => 'nullable|string|max:100',
            'zoom_topic' => 'nullable|string|max:255',
            'zoom_join_url' => 'nullable|string|max:500',
            'zoom_passcode' => 'nullable|string|max:100',
        ]);

        $doc = null;
        if (! empty($data['source_document_id'])) {
            $doc = RoomConsumptionRequest::find($data['source_document_id']);
        }
        if (! $doc && ! empty($data['it_wo_id'])) {
            $doc = RoomConsumptionRequest::where('it_wo_id', (string) $data['it_wo_id'])->first();
        }

        if (! $doc) {
            return response()->json([
                'success' => false,
                'message' => 'Room consumption request not found for callback payload',
            ], 404);
        }

        $client->applyCallbackPayload($doc, $data);

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $doc->id,
                'it_wo_id' => $doc->fresh()->it_wo_id,
                'zoom_meeting_id' => $doc->fresh()->zoom_meeting_id,
                'zoom_sync_status' => $doc->fresh()->zoom_sync_status,
            ],
        ]);
    }
}
