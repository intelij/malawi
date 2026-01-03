<?php

namespace App\Exports;

use App\Models\User;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;

class UsersWithPaymentsExport implements FromQuery, WithHeadings
{
    protected $reference;

    // Constructor to accept the reference filter value
    public function __construct($reference)
    {
        $this->reference = $reference;
    }

    public function query()
    {
        return User::query()
            ->join('payments', 'users.id', '=', 'payments.user_id')
            ->select(
                'payments.reference',
                'users.first_name',
                'users.last_name',
                'users.phone',
                'users.membership_number',
                'payments.verified as PaymentVerified'
            )
            ->where('payments.reference', $this->reference)
            ->where('payments.verified', 1);
    }

    public function headings(): array
    {
        return [
            'Reference',
            'First Name',
            'Last Name',
            'Phone',
            'Membership Number',
            'Payment Verified',
        ];
    }
}
