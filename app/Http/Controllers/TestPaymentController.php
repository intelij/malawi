<?php

namespace App\Http\Controllers;

use App\Models\Fundraiser;
use App\Models\Payment;
use Illuminate\Http\Request;

class TestPaymentController extends Controller
{

    public function createInvoice(Fundraiser $fundraiser, Payment $payment): View
    {
        // First query to get the already paid invoices references
        $alreadyPaidInvoices = $payment->where('user_id', auth()->user()->id)
            ->where('payment_type', 'Bereavement Fund')
            ->whereNotNull('fundraiser_id')
            ->pluck('fundraiser_id');

        // Unpaid fundraisers
        $fundraisers = Fundraiser::leftJoin('users', 'users.id', '=', 'fundraisers.user_id')
            ->leftJoin('payments', 'payments.fundraiser_id', '=', 'fundraisers.id')
            ->where('payments.payment_type', '!=', 'Bereavement Fund')
            ->whereNotIn('fundraisers.id', $alreadyPaidInvoices)
            ->select('fundraisers.*', 'fundraisers.id as fund_id', 'users.*', 'payments.*')
            ->get();

        // $user_registerd = Auth::getUser()->created_at;
        $user_registered = Auth::getUser()->created_at->format('Y-m-d H:i:s');

        // $unpaidFundraisers = Fundraiser::with(['user', 'payments'])->whereNotIn('fundraisers.id', $alreadyPaidInvoices)->get();
        $unpaidFundraisers = Fundraiser::with(['user', 'payments'])
            ->whereNotIn('fundraisers.id', $alreadyPaidInvoices)
            ->where('fundraisers.created_at', '>=', $user_registered)
            ->get();


        // foreach ($unpaidFundraisers as $k => $v) {
        //     if (Auth::getUser()->created_at->isBefore($v->created_at)) {
        //         dump($v->created_at->format('Ymd'));
        //     }
        // }

        // dump($user_registered, $fundraisers, $unpaidFundraisers);

        // $latestFundraiser = $fundraisers->first();
        $latestFundraiser = $unpaidFundraisers->first();

        // Get all paid fundraisers
        $paidFundraisers = $payment
            ->where('user_id', auth()->id())
            ->whereNotNull('fundraiser_id')
            ->pluck('fundraiser_id');

        $totalFundraisers = $unpaidFundraisers->count();

        return view('invoice.create', compact('fundraisers', 'latestFundraiser', 'totalFundraisers', 'paidFundraisers', 'alreadyPaidInvoices', 'unpaidFundraisers'));
    }

}
