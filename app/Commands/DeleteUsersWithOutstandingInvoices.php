<?php

namespace App\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Vanguard\Fundraiser;
use Vanguard\Payment;
use Vanguard\User;

class DeleteUsersWithOutstandingInvoices extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:delete-users-with-outstanding-invoices';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete all users who have more than 3 outstanding invoices';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $users = User::all();

        foreach ($users as $user) {
            $missedPayments = 0;

            // Find fundraisers created after user joined (created_at)
            $fundraisers = Fundraiser::where('created_at', '>', $user->created_at)
                ->where('user_id', '!=', $user->id) // exclude their own fundraisers
                ->get();

            foreach ($fundraisers as $fundraiser) {
                $hasPaid = Payment::where('user_id', $user->id)
                    ->where('reference', $fundraiser->membership_number)
                    ->exists();

                if (! $hasPaid) {
                    $missedPayments++;
                }
            }

            if ($missedPayments >= 3) {
                $this->info("Deleting user: {$user->id} - {$user->email}, missed {$missedPayments} payments");
                $user->delete();
            }
        }

        $this->info('Command executed successfully.');
    }
}
