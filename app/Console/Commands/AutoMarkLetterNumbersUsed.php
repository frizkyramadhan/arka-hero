<?php

namespace App\Console\Commands;

use App\Models\LetterNumber;
use Carbon\Carbon;
use Illuminate\Console\Command;

class AutoMarkLetterNumbersUsed extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'letter-numbers:auto-mark-used {--days=3 : Number of days after creation to mark as used}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Automatically mark reserved letter numbers as used after specified days';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $days = $this->option('days');

        $this->info("🔍 Checking for reserved letter numbers older than {$days} days...");

        // Find letter numbers that are reserved and older than specified days
        $cutoffDate = Carbon::now()->subDays($days);

        $reservedLetterNumbers = LetterNumber::where('status', 'reserved')
            ->where('created_at', '<=', $cutoffDate)
            ->get();

        if ($reservedLetterNumbers->isEmpty()) {
            $this->info("✅ No reserved letter numbers found that are older than {$days} days.");
            return;
        }

        $this->info("📋 Found {$reservedLetterNumbers->count()} reserved letter numbers to mark as used:");
        $this->table(
            ['ID', 'Letter Number', 'Category', 'Created At'],
            $reservedLetterNumbers->map(function ($letter) {
                return [
                    $letter->id,
                    $letter->letter_number,
                    $letter->category->category_name ?? 'N/A',
                    $letter->created_at->format('Y-m-d H:i:s')
                ];
            })->toArray()
        );

        $this->info("🔄 Processing letter numbers...");

        $updatedCount = 0;
        foreach ($reservedLetterNumbers as $letterNumber) {
            $letterNumber->update([
                'status' => 'used',
                'used_at' => now(),
                'used_by' => null, // Since this is automatic, no specific user
            ]);
            $updatedCount++;
        }

        $this->info("✅ Successfully marked {$updatedCount} letter numbers as used.");
        $this->comment("📅 Next run will check for letter numbers older than {$days} days from now.");
    }
}
