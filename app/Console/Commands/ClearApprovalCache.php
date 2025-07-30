<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class ClearApprovalCache extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'approval:clear-cache {--user-id= : Clear cache for specific user ID}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clear approval cache for pending approvals count';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $userId = $this->option('user-id');

        if ($userId) {
            // Clear cache for specific user
            $cacheKey = 'pending_approvals_' . $userId;
            Cache::forget($cacheKey);
            $this->info("Cache cleared for user ID: {$userId}");
        } else {
            // Clear all approval caches
            $keys = Cache::get('approval_cache_keys', []);
            foreach ($keys as $key) {
                Cache::forget($key);
            }
            $this->info('All approval caches cleared');
        }

        return 0;
    }
}
