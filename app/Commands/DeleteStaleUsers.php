<?php

namespace App\Commands;

use Illuminate\Console\Command;
use Vanguard\User; // adjust if your User model is in another namespace
use Carbon\Carbon;

class DeleteStaleUsers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * Run with: php artisan app:delete-stale-users
     */
    protected $signature = 'app:delete-stale-users';

    /**
     * The console command description.
     */
    protected $description = 'Soft delete stale users (no payments and older than cutoff), excluding Admins';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $cutoffDate = now()->subMonths(setting('stale_cutoff_period', 3));

        // Select users older than cutoff with no payments and role_id != 1 (exclude Admins)
        $staleUsers = User::where('created_at', '<', $cutoffDate)
            ->whereNotIn('id', function ($query) {
                $query->selectRaw('DISTINCT user_id')->from('payments');
            })
            ->where('role_id', '!=', 1) // exclude Admins by role_id
            ->get();

        $count = $staleUsers->count();

        foreach ($staleUsers as $user) {
            $user->delete(); // soft delete if model uses SoftDeletes
        }

        $this->info("Soft deleted {$count} stale users (excluding Admins).");
    }

}
