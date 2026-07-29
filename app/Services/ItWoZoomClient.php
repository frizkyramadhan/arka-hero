<?php

namespace App\Services;

use App\Models\RoomConsumptionRequest;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ItWoZoomClient
{
    public function isTrialMode(): bool
    {
        return empty(config('it_wo.base_url'));
    }

    /**
     * After RCR is submitted (not draft) and need_zoom=true: create IT WO (idempotent).
     */
    public function dispatchOnSubmit(RoomConsumptionRequest $doc): void
    {
        if (! $doc->need_zoom) {
            return;
        }

        if ($doc->status === RoomConsumptionRequest::STATUS_DRAFT) {
            return;
        }

        if (! empty($doc->it_wo_id) && ! in_array($doc->zoom_sync_status, ['failed', 'error'], true)) {
            return;
        }

        $result = $this->createZoomMeetingRequest($doc);

        if (! ($result['success'] ?? false)) {
            $doc->update(['zoom_sync_status' => 'failed']);
            Log::warning('IT WO Zoom create failed after RCR submit', [
                'rcr_id' => $doc->id,
                'message' => $result['message'] ?? 'unknown',
            ]);

            return;
        }

        $data = $result['data'] ?? [];
        $doc->update([
            'it_wo_id' => isset($data['it_wo_id']) ? (string) $data['it_wo_id'] : null,
            'it_wo_number' => $data['it_wo_number'] ?? null,
            'zoom_sync_status' => $this->mapApiStatus($data['status'] ?? 'open'),
        ]);
    }

    /** @deprecated Use dispatchOnSubmit() */
    public function dispatchAfterApproval(RoomConsumptionRequest $doc): void
    {
        $this->dispatchOnSubmit($doc);
    }

    /**
     * First RCR approval step → IT WO app_status_l1 = Approved.
     */
    public function syncApproveL1(RoomConsumptionRequest $doc): void
    {
        if (! $doc->need_zoom || empty($doc->it_wo_id)) {
            return;
        }

        if ($this->isTrialMode() || Str::startsWith((string) $doc->it_wo_id, 'trial-')) {
            Log::info('IT WO Zoom approve_l1 skipped (trial mode)', [
                'rcr_id' => $doc->id,
                'it_wo_id' => $doc->it_wo_id,
            ]);

            return;
        }

        $result = $this->putZoomMeetingAction((string) $doc->it_wo_id, 'approve_l1', $doc->id);
        if (! ($result['success'] ?? false)) {
            Log::warning('IT WO Zoom approve_l1 failed after first RCR approval', [
                'rcr_id' => $doc->id,
                'it_wo_id' => $doc->it_wo_id,
                'message' => $result['message'] ?? 'unknown',
            ]);
        }
    }

    /**
     * RCR rejected → IT WO status = Canceled + L1 Disapproved.
     */
    public function syncCancelOnReject(RoomConsumptionRequest $doc): void
    {
        if (! $doc->need_zoom || empty($doc->it_wo_id)) {
            return;
        }

        if ($this->isTrialMode() || Str::startsWith((string) $doc->it_wo_id, 'trial-')) {
            $doc->update(['zoom_sync_status' => 'cancelled']);
            Log::info('IT WO Zoom cancel skipped (trial mode); local status set cancelled', [
                'rcr_id' => $doc->id,
                'it_wo_id' => $doc->it_wo_id,
            ]);

            return;
        }

        $result = $this->putZoomMeetingAction((string) $doc->it_wo_id, 'cancel', $doc->id);
        if (! ($result['success'] ?? false)) {
            Log::warning('IT WO Zoom cancel failed after RCR reject', [
                'rcr_id' => $doc->id,
                'it_wo_id' => $doc->it_wo_id,
                'message' => $result['message'] ?? 'unknown',
            ]);

            return;
        }

        $doc->update(['zoom_sync_status' => 'cancelled']);
    }

    /**
     * PUT /api/v1/zoom-meeting-requests/{id} with action approve_l1|cancel.
     *
     * @return array{success: bool, data?: array<string, mixed>, message?: string}
     */
    private function putZoomMeetingAction(string $itWoId, string $action, string $rcrId): array
    {
        if (! ctype_digit($itWoId)) {
            return ['success' => false, 'message' => 'Invalid IT WO id format.'];
        }

        $url = rtrim((string) config('it_wo.base_url'), '/')
            .'/api/v1/zoom-meeting-requests/'.urlencode($itWoId);

        try {
            $response = Http::timeout(30)
                ->withHeaders($this->headers())
                ->asJson()
                ->put($url, ['action' => $action]);

            $body = $response->json();
            if (! is_array($body)) {
                $body = [];
            }

            Log::info('IT WO Zoom PUT '.$action.' response', [
                'rcr_id' => $rcrId,
                'it_wo_id' => $itWoId,
                'http' => $response->status(),
                'body' => $body,
            ]);

            if ($response->successful() && ($body['success'] ?? false)) {
                return [
                    'success' => true,
                    'data' => $body['data'] ?? $body,
                ];
            }

            return [
                'success' => false,
                'message' => $this->extractErrorMessage($body, $response->status()),
            ];
        } catch (\Throwable $e) {
            Log::error('IT WO Zoom PUT '.$action.' exception', [
                'rcr_id' => $rcrId,
                'it_wo_id' => $itWoId,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'message' => 'IT WO connection failed: '.$e->getMessage(),
            ];
        }
    }

    /**
     * Apply callback / GET sync payload onto the matching RCR.
     *
     * @param  array<string, mixed>  $data
     */
    public function applyCallbackPayload(RoomConsumptionRequest $doc, array $data): void
    {
        $updates = [];

        if (! empty($data['it_wo_id'])) {
            $updates['it_wo_id'] = (string) $data['it_wo_id'];
        }
        if (! empty($data['it_wo_number'])) {
            $updates['it_wo_number'] = $data['it_wo_number'];
        }
        if (! empty($data['zoom_meeting_id'])) {
            $updates['zoom_meeting_id'] = $data['zoom_meeting_id'];
        }
        if (! empty($data['zoom_topic'])) {
            $updates['zoom_topic'] = $data['zoom_topic'];
        }
        if (! empty($data['zoom_join_url'])) {
            $updates['zoom_join_url'] = $data['zoom_join_url'];
        }
        if (! empty($data['zoom_passcode'])) {
            $updates['zoom_passcode'] = $data['zoom_passcode'];
        }

        $status = $data['status'] ?? null;
        if ($status !== null) {
            $updates['zoom_sync_status'] = $this->mapApiStatus($status);
        } elseif (! empty($data['zoom_meeting_id'])) {
            $updates['zoom_sync_status'] = 'completed';
        }

        if ($updates !== []) {
            $doc->update($updates);
        }
    }

    /**
     * POST create Zoom WO on rest-server (cat 8 / subcat 35 handled server-side).
     *
     * @return array{success: bool, data?: array<string, mixed>, message?: string, trial?: bool}
     */
    public function createZoomMeetingRequest(RoomConsumptionRequest $doc): array
    {
        $doc->loadMissing([
            'project',
            'meetingRoom',
            'requestedBy.employee',
            'requestedBy.administration.position.department',
            'approvalPlans.approver.employee',
            'approvalPlans.approver.administration.position.department',
        ]);

        if ($this->isTrialMode()) {
            return $this->trialCreateResponse($doc);
        }

        $payload = $this->buildPayload($doc);
        $preflight = $this->validatePayload($payload);
        if ($preflight !== null) {
            return ['success' => false, 'message' => $preflight];
        }

        $url = rtrim((string) config('it_wo.base_url'), '/').'/api/v1/zoom-meeting-requests';

        try {
            $response = Http::timeout(30)
                ->withHeaders($this->headers())
                ->asJson()
                ->post($url, $payload);

            $body = $response->json();
            if (! is_array($body)) {
                $body = [];
            }

            Log::info('IT WO Zoom create response', [
                'rcr_id' => $doc->id,
                'http' => $response->status(),
                'body' => $body,
            ]);

            if ($response->successful() && ($body['success'] ?? false)) {
                return [
                    'success' => true,
                    'data' => $body['data'] ?? $body,
                    'idempotent' => (bool) ($body['idempotent'] ?? false),
                ];
            }

            return [
                'success' => false,
                'message' => $this->extractErrorMessage($body, $response->status()),
            ];
        } catch (\Throwable $e) {
            Log::error('IT WO Zoom create exception', [
                'rcr_id' => $doc->id,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'message' => 'IT WO connection failed: '.$e->getMessage(),
            ];
        }
    }

    /**
     * GET Zoom Meeting ID availability for accounts 131/132/134 on a date.
     * Same data as IT WO Zoom availability widget.
     *
     * @return array{success: bool, data?: array<string, mixed>, message?: string, trial?: bool}
     */
    public function getZoomAvailability(string $date): array
    {
        $date = substr($date, 0, 10);
        if (! preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            return ['success' => false, 'message' => 'Invalid date format. Use YYYY-MM-DD.'];
        }

        if (! $this->isTrialMode()) {
            $base = rtrim((string) config('it_wo.base_url'), '/');
            $paths = [
                '/api/v1/zoom-meeting-availability',
                '/api/v1/zoom-meeting-requests/availability',
            ];
            $lastMessage = null;

            foreach ($paths as $path) {
                try {
                    $response = Http::withHeaders($this->headers())
                        ->timeout(30)
                        ->acceptJson()
                        ->get($base.$path, ['date' => $date]);

                    $body = $response->json() ?? [];

                    if ($response->successful() && ($body['success'] ?? false)) {
                        return [
                            'success' => true,
                            'source' => 'api',
                            'data' => $body['data'] ?? [],
                        ];
                    }

                    if ($response->status() === 404) {
                        $lastMessage = 'Zoom availability API not found (HTTP 404) at '.$base.$path;
                        continue;
                    }

                    $lastMessage = $this->extractErrorMessage($body, $response->status());
                } catch (\Throwable $e) {
                    Log::warning('IT WO Zoom availability API failed', [
                        'date' => $date,
                        'path' => $path,
                        'error' => $e->getMessage(),
                    ]);
                    $lastMessage = 'IT WO connection failed: '.$e->getMessage();
                }
            }

            $local = $this->getZoomAvailabilityFromLocalDb($date);
            if ($local['success'] ?? false) {
                Log::info('IT WO Zoom availability served from local it_wo DB fallback', [
                    'date' => $date,
                    'api_message' => $lastMessage,
                ]);

                return $local;
            }

            return [
                'success' => false,
                'message' => $lastMessage
                    ?? 'Failed to load Zoom availability from IT WO API and local database.',
            ];
        }

        $local = $this->getZoomAvailabilityFromLocalDb($date);
        if ($local['success'] ?? false) {
            return $local;
        }

        return [
            'success' => true,
            'trial' => true,
            'source' => 'trial',
            'data' => [
                'date' => $date,
                'accounts' => $this->trialAvailabilityAccounts(),
            ],
        ];
    }

    /**
     * @return array{success: bool, data?: array<string, mixed>, message?: string, source?: string}
     */
    private function getZoomAvailabilityFromLocalDb(string $date): array
    {
        try {
            $service = app(\App\Support\Zoom\ZoomAvailabilityService::class);
            if (! $service->isAvailable()) {
                return ['success' => false, 'message' => 'Local it_wo database is not reachable.'];
            }

            return [
                'success' => true,
                'source' => 'local_db',
                'data' => $service->getAvailability($date),
            ];
        } catch (\Throwable $e) {
            Log::warning('IT WO Zoom local DB availability failed', [
                'date' => $date,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'message' => 'Local it_wo availability failed: '.$e->getMessage(),
            ];
        }
    }
    /**
     * @return array{success: bool, data?: array<string, mixed>, message?: string, trial?: bool}
     */
    public function syncZoomMeetingRequest(RoomConsumptionRequest $doc): array
    {
        if (empty($doc->it_wo_id)) {
            return ['success' => false, 'message' => 'No IT Work Order linked to this request.'];
        }

        if ($this->isTrialMode() || Str::startsWith((string) $doc->it_wo_id, 'trial-')) {
            return $this->trialSyncResponse($doc);
        }

        $url = rtrim((string) config('it_wo.base_url'), '/')
            .'/api/v1/zoom-meeting-requests/'.urlencode((string) $doc->it_wo_id);

        try {
            $response = Http::timeout(30)
                ->withHeaders($this->headers())
                ->acceptJson()
                ->get($url);

            $body = $response->json();
            if (! is_array($body)) {
                $body = [];
            }

            Log::info('IT WO Zoom sync response', [
                'rcr_id' => $doc->id,
                'it_wo_id' => $doc->it_wo_id,
                'http' => $response->status(),
                'body' => $body,
            ]);

            if ($response->successful() && ($body['success'] ?? false)) {
                return [
                    'success' => true,
                    'data' => $body['data'] ?? $body,
                ];
            }

            return [
                'success' => false,
                'message' => $this->extractErrorMessage($body, $response->status()),
            ];
        } catch (\Throwable $e) {
            Log::error('IT WO Zoom sync exception', [
                'rcr_id' => $doc->id,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'message' => 'IT WO connection failed: '.$e->getMessage(),
            ];
        }
    }

    /**
     * DELETE Zoom WO on rest-server (debug reset).
     *
     * @return array{success: bool, message?: string, skipped?: bool}
     */
    public function deleteZoomMeetingRequest(?string $itWoId): array
    {
        if ($itWoId === null || $itWoId === '') {
            return ['success' => true, 'skipped' => true, 'message' => 'No IT WO id to delete.'];
        }

        if ($this->isTrialMode() || Str::startsWith($itWoId, 'trial-')) {
            return ['success' => true, 'skipped' => true, 'message' => 'Trial IT WO skipped on rest-server.'];
        }

        if (! ctype_digit($itWoId)) {
            return ['success' => false, 'message' => 'Invalid IT WO id format.'];
        }

        $url = rtrim((string) config('it_wo.base_url'), '/')
            .'/api/v1/zoom-meeting-requests/'.urlencode($itWoId);

        try {
            $response = Http::timeout(30)
                ->withHeaders($this->headers())
                ->acceptJson()
                ->delete($url);

            $body = $response->json();
            if (! is_array($body)) {
                $body = [];
            }

            Log::info('IT WO Zoom delete response', [
                'it_wo_id' => $itWoId,
                'http' => $response->status(),
                'body' => $body,
            ]);

            if ($response->successful() && ($body['success'] ?? false)) {
                return ['success' => true, 'message' => $body['message'] ?? 'IT Work Order deleted.'];
            }

            if ($response->status() === 404) {
                return [
                    'success' => true,
                    'skipped' => true,
                    'message' => 'IT Work Order not found on rest-server (already deleted).',
                ];
            }

            return [
                'success' => false,
                'message' => $this->extractErrorMessage($body, $response->status()),
            ];
        } catch (\Throwable $e) {
            Log::error('IT WO Zoom delete exception', [
                'it_wo_id' => $itWoId,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'message' => 'IT WO connection failed: '.$e->getMessage(),
            ];
        }
    }

    /**
     * Clear IT WO / Zoom fields on RCR back to pre-request state.
     */
    public function resetLocalZoomItWoState(RoomConsumptionRequest $doc): void
    {
        $doc->update([
            'it_wo_id' => null,
            'it_wo_number' => null,
            'zoom_meeting_id' => null,
            'zoom_topic' => null,
            'zoom_join_url' => null,
            'zoom_passcode' => null,
            'zoom_sync_status' => $doc->need_zoom ? 'pending' : 'not_required',
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function buildPayload(RoomConsumptionRequest $doc): array
    {
        $requester = $doc->requestedBy;
        $admin = $requester?->administration;
        $requesterPosition = $admin?->position;
        $requesterName = $requester?->employee?->fullname
            ?: $requester?->name;

        $firstApprover = $doc->approvalPlans
            ->sortBy('approval_order')
            ->first();
        $acknowledgeNik = null;
        $approverPosition = null;
        $acknowledgeName = null;
        $acknowledgeEmail = null;
        if ($firstApprover) {
            $approver = $firstApprover->approver;
            $approverAdmin = $approver?->administration;
            $acknowledgeNik = $approverAdmin?->nik;
            $approverPosition = $approverAdmin?->position;
            $acknowledgeName = $approver?->employee?->fullname
                ?: $approver?->name;
            $acknowledgeEmail = $approver?->email;
        }

        $start = $doc->start_time
            ? \Carbon\Carbon::parse($doc->start_time)->format('H:i')
            : null;
        $end = $doc->end_time
            ? \Carbon\Carbon::parse($doc->end_time)->format('H:i')
            : null;

        return [
            'source_system' => 'arka-hero',
            'source_document_type' => 'room_consumption_request',
            'source_document_id' => $doc->id,
            'source_document_number' => $doc->request_number,
            'requester_nik' => $admin?->nik !== null && $admin?->nik !== ''
                ? (string) $admin->nik
                : null,
            'requester_name' => $requesterName,
            'requester_email' => $requester?->email,
            'requester_position_name' => $requesterPosition?->position_name,
            'requester_department_name' => $requesterPosition?->department?->department_name,
            'meeting_title' => $doc->meeting_title,
            'meeting_date' => $doc->start_date?->format('Y-m-d'),
            'end_date' => $doc->end_date?->format('Y-m-d'),
            'start_time' => $start,
            'end_time' => $end,
            'attendees_count' => $doc->attendees_count,
            'room_name' => $doc->meetingRoom?->room_name,
            'project_code' => $doc->project?->project_code,
            'notes' => $doc->notes,
            'acknowledge_nik' => $acknowledgeNik !== null && $acknowledgeNik !== ''
                ? (string) $acknowledgeNik
                : null,
            'acknowledge_name' => $acknowledgeName,
            'acknowledge_email' => $acknowledgeEmail,
            'acknowledge_position_name' => $approverPosition?->position_name,
            'acknowledge_department_name' => $approverPosition?->department?->department_name,
        ];
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function validatePayload(array $payload): ?string
    {
        if (empty($payload['source_document_id'])) {
            return 'source_document_id kosong.';
        }
        if (empty($payload['requester_nik'])) {
            return 'NIK requester tidak ditemukan di HERO. Pastikan administrasi aktif terisi NIK.';
        }
        if (empty($payload['project_code'])) {
            return 'Project code kosong.';
        }
        if (empty($payload['meeting_title'])) {
            return 'Meeting title wajib diisi.';
        }
        if (empty($payload['meeting_date'])) {
            return 'Meeting date wajib diisi.';
        }
        if (empty($payload['requester_email'])) {
            return 'Email requester wajib diisi (dipakai sebagai contact person IT WO).';
        }

        return null;
    }

    /**
     * @param  array<string, mixed>  $body
     */
    private function extractErrorMessage(array $body, int $status): string
    {
        if (! empty($body['message']) && is_string($body['message'])) {
            return $body['message'];
        }

        if (! empty($body['errors']) && is_array($body['errors'])) {
            return implode('; ', array_map('strval', $body['errors']));
        }

        return 'IT WO API error: HTTP '.$status;
    }

    private function mapApiStatus(?string $status): string
    {
        return match ($status) {
            'completed', 'done' => 'completed',
            'failed', 'error', 'cancelled' => 'failed',
            'processing', 'in_progress' => 'processing',
            'open', 'new' => 'open',
            default => 'pending',
        };
    }

    /**
     * @return array<string, string>
     */
    private function headers(): array
    {
        $headers = [
            'Accept' => 'application/json',
            'X-Source' => 'arka-hero',
        ];

        $apiKey = config('it_wo.api_key');
        if ($apiKey) {
            $headers['X-API-Key'] = $apiKey;
            $headers['arka-key'] = $apiKey;
        }

        return $headers;
    }

    /**
     * @return array{success: true, data: array<string, mixed>, trial: true}
     */
    private function trialCreateResponse(RoomConsumptionRequest $doc): array
    {
        $suffix = strtoupper(substr(str_replace('-', '', $doc->id), 0, 8));

        return [
            'success' => true,
            'trial' => true,
            'data' => [
                'it_wo_id' => 'trial-'.$doc->id,
                'it_wo_number' => 'ITWO-TRIAL-'.$suffix,
                'status' => 'open',
                'id_kategori' => 8,
                'id_subkat' => 35,
            ],
        ];
    }

    /**
     * @return array{success: true, data: array<string, mixed>, trial: true}
     */
    private function trialSyncResponse(RoomConsumptionRequest $doc): array
    {
        $meetingId = '9'.str_pad((string) abs(crc32($doc->id)), 10, '0', STR_PAD_LEFT);

        return [
            'success' => true,
            'trial' => true,
            'data' => [
                'status' => 'completed',
                'zoom_meeting_id' => $meetingId,
                'zoom_topic' => $doc->meeting_title ?: 'Trial Zoom Meeting',
                'zoom_join_url' => 'https://zoom.us/j/'.$meetingId,
                'zoom_passcode' => 'trial'.substr((string) abs(crc32($doc->id)), 0, 4),
                'it_wo_number' => $doc->it_wo_number,
            ],
        ];
    }

    /**
     * Trial-mode catalog for availability UI when IT_WO_BASE_URL is empty.
     *
     * @return array<string, array<string, mixed>>
     */
    private function trialAvailabilityAccounts(): array
    {
        $catalog = [
            '131' => [
                'ARKANANTA131 INTERVIEW ROOM',
                'ARKNANTA 131 Center Room Meeting',
                'ARKNANTA Asesmen Room 131',
            ],
            '132' => [
                'ARKA General Meeting Room',
                'ARKANANTA INTERVIEW ROOM 132',
                'Assessment Room 132',
            ],
            '134' => [
                'ARKANANTA INTERVIEW ROOM 134',
                'General Meeting Room 134',
            ],
        ];

        $accounts = [];
        foreach ($catalog as $code => $names) {
            $accounts[$code] = [
                'account' => $code,
                'room_names' => $names,
                'status' => 'available',
                'slots' => [],
                'bookings' => [],
            ];
        }

        return $accounts;
    }
}
