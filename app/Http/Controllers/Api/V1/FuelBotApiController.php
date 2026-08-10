<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\FuelBotSubmission;
use App\Models\FuelBotSubscriber;
use App\Services\FuelBotIngestService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class FuelBotApiController extends Controller
{
    /**
     * GET /api/v1/fuel-bot/whitelist/{telegramUserId}
     */
    public function whitelistShow(int $telegramUserId): JsonResponse
    {
        $sub = FuelBotSubscriber::findActiveByTelegramId($telegramUserId);
        if (! $sub) {
            return response()->json(['success' => true, 'allowed' => false]);
        }

        $sub->load('user:id,name,email,username');

        return response()->json([
            'success' => true,
            'allowed' => true,
            'data' => [
                'user_id' => $sub->user_id,
                'user_name' => $sub->user?->name,
                'user_email' => $sub->user?->email,
                'telegram_user_id' => $sub->telegram_user_id,
                'telegram_username' => $sub->telegram_username,
            ],
        ]);
    }

    /**
     * POST /api/v1/fuel-bot/fuel-records
     * Multipart create from bot (idempotent via client_uuid).
     */
    public function storeFuelRecord(Request $request, FuelBotIngestService $ingest): JsonResponse
    {
        $data = $request->validate([
            'client_uuid' => ['required', 'uuid'],
            'telegram_user_id' => ['required', 'integer'],
            'chat_id' => ['nullable', 'integer'],
            'vehicle_id' => ['nullable', 'uuid', 'exists:vehicles,id'],
            'vehicle_code' => ['nullable', 'string', 'max:50'],
            'fuel_date' => ['required', 'date'],
            'odometer' => ['required', 'integer', 'min:0'],
            'fuel_type' => ['required', 'string', 'max:50'],
            'quantity' => ['required', 'numeric', 'min:0.01'],
            'price_per_liter' => ['required', 'numeric', 'min:0'],
            'total_cost' => ['nullable', 'numeric', 'min:0'],
            'fuel_station' => ['nullable', 'string', 'max:255'],
            'receipt_number' => ['nullable', 'string', 'max:100'],
            'notes' => ['nullable', 'string'],
            'ai_model' => ['nullable', 'string', 'max:100'],
            'ai_raw_json' => ['nullable'],
            'receipt_image' => ['required', 'file', 'max:8192', 'mimes:jpg,jpeg,png,webp'],
        ]);

        $subscriber = FuelBotSubscriber::findActiveByTelegramId((int) $data['telegram_user_id']);
        if (! $subscriber) {
            return response()->json(['success' => false, 'message' => 'Telegram user is not whitelisted.'], 403);
        }

        $existing = FuelBotSubmission::where('client_uuid', $data['client_uuid'])->first();
        if ($existing?->fuel_record_id) {
            return response()->json([
                'success' => true,
                'data' => [
                    'fuel_record_id' => $existing->fuel_record_id,
                    'status' => 'submitted',
                    'client_uuid' => $existing->client_uuid,
                ],
            ]);
        }

        $file = $request->file('receipt_image');
        $path = $file->storeAs(
            'fuel_bot_inbox/'.now()->format('Ymd'),
            Str::uuid().'.'.$file->getClientOriginalExtension(),
            'private'
        );

        $parsed = [
            'vehicle_id' => $data['vehicle_id'] ?? null,
            'vehicle_code' => $data['vehicle_code'] ?? null,
            'fuel_date' => $data['fuel_date'],
            'odometer' => (int) $data['odometer'],
            'fuel_type' => $data['fuel_type'],
            'quantity' => (float) $data['quantity'],
            'price_per_liter' => (float) $data['price_per_liter'],
            'total_cost' => isset($data['total_cost']) ? (float) $data['total_cost'] : null,
            'fuel_station' => $data['fuel_station'] ?? null,
            'receipt_number' => $data['receipt_number'] ?? null,
            'notes' => $data['notes'] ?? null,
            'ai_raw_json' => $data['ai_raw_json'] ?? null,
        ];

        if (! $parsed['vehicle_id'] && $parsed['vehicle_code']) {
            // Let ingest resolve
        }

        $submission = FuelBotSubmission::updateOrCreate(
            ['client_uuid' => $data['client_uuid']],
            [
                'telegram_user_id' => (int) $data['telegram_user_id'],
                'chat_id' => (int) ($data['chat_id'] ?? 0),
                'user_id' => $subscriber->user_id,
                'status' => FuelBotSubmission::STATUS_PUSHING,
                'receipt_path' => $path,
                'parsed_json' => $parsed,
                'ai_model' => $data['ai_model'] ?? null,
                'confirmed_at' => now(),
            ]
        );

        try {
            $record = $ingest->syncSubmission($submission);

            return response()->json([
                'success' => true,
                'data' => [
                    'fuel_record_id' => $record->id,
                    'status' => $record->status,
                    'client_uuid' => $submission->client_uuid,
                ],
            ], 201);
        } catch (\Throwable $e) {
            $submission->update([
                'status' => FuelBotSubmission::STATUS_FAILED,
                'error_message' => $e->getMessage(),
            ]);

            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }
}
