<?php

namespace App\Support;

use App\Models\User;
use Illuminate\Support\Facades\DB;

class MembershipNumberGenerator
{
    public static function generate(): string
    {
        return DB::transaction(function () {
            // Lock the row to prevent duplicates under concurrency
            $lastUser = User::whereNotNull('membership_number')
                ->orderBy('id', 'desc')
                ->lockForUpdate()
                ->first();

            $lastNumber = 0;

            if ($lastUser && preg_match('/MW(\d+)/', $lastUser->membership_number, $matches)) {
                $lastNumber = (int) $matches[1];
            }

            $nextNumber = $lastNumber + 1;

            return 'MW' . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
        });
    }
}
