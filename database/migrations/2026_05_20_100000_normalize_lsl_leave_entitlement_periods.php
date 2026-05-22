<?php

use App\Services\LeaveEntitlementLSLPeriodNormalizer;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Log;

return new class extends Migration
{
    /**
     * Normalize existing LSL leave entitlements to multi-year periods
     * based on leave type eligible_after_years and service DOH.
     */
    public function up(): void
    {
        Log::info('Starting LSL leave entitlement period normalization...');

        $stats = app(LeaveEntitlementLSLPeriodNormalizer::class)->normalize();

        Log::info('LSL leave entitlement period normalization completed.', $stats);
    }

    /**
     * Irreversible: previous 1-year LSL periods are not restored.
     */
    public function down(): void
    {
        Log::warning('LSL leave entitlement period normalization cannot be rolled back automatically.');
    }
};
