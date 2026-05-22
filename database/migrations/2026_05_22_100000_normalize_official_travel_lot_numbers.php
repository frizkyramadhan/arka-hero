<?php

use App\Services\OfficialTravelLotNumberNormalizer;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Log;

return new class extends Migration
{
    /**
     * Normalize existing LOT numbers to include project code:
     * ARKA/{Letter Number}/HR/{month}/{year}
     * -> ARKA/{Letter Number}/HR-{Project Code}/{month}/{year}
     */
    public function up(): void
    {
        Log::info('Starting official travel LOT number normalization...');

        $stats = app(OfficialTravelLotNumberNormalizer::class)->normalize();

        Log::info('Official travel LOT number normalization completed.', $stats);
    }

    /**
     * Irreversible: previous LOT number format is not restored automatically.
     */
    public function down(): void
    {
        Log::warning('Official travel LOT number normalization cannot be rolled back automatically.');
    }
};
