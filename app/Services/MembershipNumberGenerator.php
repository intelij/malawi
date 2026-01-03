<?php

namespace App\Services;

use App\Models\User;

class MembershipNumberGenerator
{
    public static function generateNumber($prefix)
    {
        // Retrieve the last used numeric part for the given prefix from the database
        $lastNumber = User::where('prefix', $prefix)->max('numeric_part');

        // Increment the last used numeric part by 1
        $nextNumber = $lastNumber + 1;

        // Pad the numeric part with leading zeros to ensure it has 6 digits
        $numericPart = str_pad($nextNumber, 4, '0', STR_PAD_LEFT);

        // Concatenate the prefix and numeric part to form the membership number
        $membershipNumber = 'MW' . $numericPart;

        return $membershipNumber;
    }

    public static function generate(User $user, string $prefix = 'MW'): string
    {
        return $prefix . '-' . str_pad($user->id, 6, '0', STR_PAD_LEFT);
    }
}
