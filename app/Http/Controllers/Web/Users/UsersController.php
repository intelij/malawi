<?php

namespace App\Http\Controllers\Web\Users;

use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use App\Events\User\Deleted;
use App\Exports\UsersWithPaymentsExport;
use App\Http\Controllers\Controller;
use App\Http\Requests\User\CreateUserRequest;
use App\Imports\UsersImport;
use App\Models\Fundraiser;
use App\Models\Payment;
use App\Models\Role;
use App\Models\User;
use App\Repositories\Country\CountryRepository;
use App\Repositories\Role\RoleRepository;
use App\Repositories\User\UserRepository;
use App\Support\Enum\UserStatus;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
// use Vanguard\Imports\UsersImport;

// use Vanguard\Exports\UsersWithPaymentsExport;
// use Maatwebsite\Excel\Facades\Excel;

class UsersController extends Controller
{
    public function __construct(private readonly UserRepository $users)
    {
    }

    public function index(Request $request): View
    {
        $users = $this->users->paginate($perPage = 20, $request->search, $request->status);

        $statuses = ['' => __('All')] + UserStatus::lists();

        $currentDate = now();
        $endDate = $currentDate->copy()->addDays(5);

        // $allowFundraiser = Carbon::parse(Auth::user()->created_at)->addMonths(setting('cooling_off_period'))->isBefore(Carbon::now());

        $fundraisers = Fundraiser::whereBetween('end_date', [$currentDate, $endDate])
        ->get();

        $fundraiser_user_ids = $fundraisers->pluck('user_id')->toArray();

        return view('user.list', compact('users', 'statuses', 'fundraiser_user_ids'));

        // return view('user.list', compact('users', 'statuses'));
    }

    public function show(User $user): View
    {
        return view('user.view', compact('user'));
    }

    public function create(CountryRepository $countryRepository, RoleRepository $roleRepository): View
    {
        return view('user.add', [
            'countries' => $this->parseCountries($countryRepository),
            'roles' => $roleRepository->lists(),
            'statuses' => UserStatus::lists(),
        ]);
    }

    /**
     * Parse countries into an array that also has a blank
     * item as first element, which will allow users to
     * leave the country field unpopulated.
     */
    private function parseCountries(CountryRepository $countryRepository): array
    {
        return [0 => __('Select a Country')] + $countryRepository->lists()->toArray();
    }

    public function store(CreateUserRequest $request): RedirectResponse
    {
        // When user is created by administrator, we will set his
        // status to Active by default.
        $data = $request->all() + [
            'status' => UserStatus::ACTIVE,
            'email_verified_at' => now(),
        ];

        $data['force_password_change'] = !!setting('password-change.enabled');

        if (! data_get($data, 'country_id')) {
            $data['country_id'] = null;
        }

        // Username should be updated only if it is provided.
        if (! data_get($data, 'username')) {
            $data['username'] = null;
        }

        $this->users->create($data);

        return redirect()->route('users.index')
            ->withSuccess(__('User created successfully.'));
    }

    public function edit(User $user, CountryRepository $countryRepository, RoleRepository $roleRepository): View
    {
        return view('user.edit', [
            'edit' => true,
            'user' => $user,
            'countries' => $this->parseCountries($countryRepository),
            'roles' => $roleRepository->lists(),
            'statuses' => UserStatus::lists(),
            'socialLogins' => $this->users->getUserSocialLogins($user->id),
        ]);
    }

    public function destroy(User $user): RedirectResponse
    {
        if ($user->is(auth()->user())) {
            return redirect()->route('users.index')
                ->withErrors(__('You cannot delete yourself.'));
        }

        $this->users->delete($user->id);

        event(new Deleted($user));

        return redirect()->route('users.index')
            ->withSuccess(__('User deleted successfully.'));
    }

    public function uploadExcel(Request $request)
    {
        $request->validate([
            'excel_file' => 'required|file|mimes:csv,txt'
        ]);

        $file = $request->file('excel_file');
        Excel::import(new UsersImport, $file);

        return back()->with('success', 'Excel file processed successfully.');
    }

    public function fundraiser(Fundraiser $fund)
    {

        // $res = $fund->whereBetween('start_date', now()->format('Y-m-d'), now()->addDays(3)->format('Y-m-d'));
        $fundraiser = $fund->with('user')->with('payments')->orderBy('id', 'desc')->get();

        return view('fundraiser.list', compact('fundraiser'));

    }

    public function newfundraiser(Request $request)
    {
        $fundraiser_days = (int) setting('default_fundraiser_length') ?? 3;
        $fundraiser = new Fundraiser();
        $fundraiser->user_id = $request->get('user_id');
        $fundraiser->start_date = now()->format('Y-m-d');
        $fundraiser->end_date =  now()->addDays($fundraiser_days)->format('Y-m-d');
        $fundraiser->amount = setting('default_contribution') ?? null;
        $fundraiser->membership_number = $request->get('reference');
        $fundraiser->created_by = auth()->id();
        $fundraiser->save();

        $fundraiserId = $fundraiser->id;

        $fullname = $request->get('full_name');

        // For the bereaved member register null payment
        if (null !== $request->get('user_id')) {
            $payment = new Payment();
            $payment->image_name = "Not Applicable";
            $payment->user_id = $request->get('user_id');
            $payment->fundraiser_id = $fundraiserId;
            $payment->payment_type = 'Bereaved';
            $payment->reference = $request->get('reference');
            $payment->save();
        }


        return redirect()->route('users.index')
        ->withSuccess(__("New Funderaiser for $fullname created successfully."));
    }

    public function destroy_fundraiser(Fundraiser $fundraiser)
    {
        if (!$fundraiser) {
            return response()->json(['message' => 'Fundraiser not found'], 404);
        }

        $fundraiser->delete();

        return redirect()->route('fundraiser.list')->with('success', 'Fundraiser deleted successfully.');
    }

    public function exportFundraiser(Fundraiser $fundraiser, Request $request)
    {
        $membershipNumber = $request->input('membership_number');

        return Excel::download(new UsersWithPaymentsExport($membershipNumber), 'users_with_payments.xlsx');

    }

    public function noActivityLogs(Request $request): View
    {

        $threeMonthsAgo = now()->subMonths(setting('stale_cutoff_period')); // Only consider accounts older than 3 months

        $users = User::whereNotIn('id', function ($query) {
                $query->selectRaw('DISTINCT user_id')
                    ->from('payments'); // Exclude users who have made payments
            })
            ->where('created_at', '<', $threeMonthsAgo) // Only users created more than 3 months ago
            ->paginate(20);


        $statuses = ['' => __('All')] + UserStatus::lists();

        $currentDate = now();
        $endDate = $currentDate->copy()->addDays(5);

        $allowFundraiser = Carbon::parse(auth()->user()->created_at)
            ->addMonths(setting('cooling_off_period'))
            ->isBefore(Carbon::now());

        $fundraisers = Fundraiser::whereBetween('end_date', [$currentDate, $endDate])->get();
        $fundraiser_user_ids = $fundraisers->pluck('user_id')->toArray();

        return view('user.stale', compact('users', 'statuses', 'fundraiser_user_ids'));
    }

    public function createInvoice(): View
    {
        $user = auth()->user();

        /*
        * 1. Get all fundraiser IDs that are already PAID
        *    (Bereavement Fund OR Bereaved)
        */
        $paidFundraiserIds = Payment::where('user_id', $user->id)
            ->whereNotNull('fundraiser_id')
            ->whereIn('payment_type', ['Bereavement Fund', 'Bereaved'])
            ->pluck('fundraiser_id');

        /*
        * 2. Get all UNPAID fundraisers for this user
        *    - Exclude paid ones
        *    - Only fundraisers created after user registration
        */
        $unpaidFundraisers = Fundraiser::with(['user', 'payments'])
            ->whereNotIn('id', $paidFundraiserIds)
            ->where('created_at', '>=', $user->created_at)
            ->orderByDesc('created_at')
            ->get();

        /*
        * 3. Latest unpaid fundraiser (true latest)
        */
        $latestFundraiser = $unpaidFundraisers->first();

        /*
        * 4. Total unpaid fundraisers
        */
        $totalFundraisers = $unpaidFundraisers->count();

        return view('invoice.create', [
            'unpaidFundraisers'   => $unpaidFundraisers,
            'latestFundraiser'    => $latestFundraiser,
            'totalFundraisers'    => $totalFundraisers,
            'paidFundraiserIds'   => $paidFundraiserIds,
        ]);
    }

    // public function createInvoice(Fundraiser $fundraiser, Payment $payment): View
    // {

    //     // First query to get the already paid invoices references
    //     $alreadyPaidInvoices = $payment->where('user_id', auth()->user()->id)
    //         ->where('payment_type', 'Bereavement Fund')
    //         ->whereNotNull('fundraiser_id')
    //         ->pluck('fundraiser_id');

    //     // Unpaid fundraisers
    //     $fundraisers = Fundraiser::leftJoin('users', 'users.id', '=', 'fundraisers.user_id')
    //         ->leftJoin('payments', 'payments.fundraiser_id', '=', 'fundraisers.id')
    //         ->where('payments.payment_type', '!=', 'Bereavement Fund')
    //         ->whereNotIn('fundraisers.id', $alreadyPaidInvoices)
    //         ->select('fundraisers.*', 'fundraisers.id as fund_id', 'users.*', 'payments.*')
    //         ->get();

    //     // $user_registerd = Auth::getUser()->created_at;
    //     $user_registered = Auth::getUser()->created_at->format('Y-m-d H:i:s');

    //     $unpaidFundraisers = Fundraiser::with(['user', 'payments'])
    //         ->whereNotIn('fundraisers.id', $alreadyPaidInvoices)
    //         ->where('fundraisers.created_at', '>=', $user_registered)
    //         ->get();

    //     $latestFundraiser = $unpaidFundraisers->first();

    //     // Get all paid fundraisers
    //     $paidFundraisers = $payment
    //         ->where('user_id', auth()->id())
    //         ->whereNotNull('fundraiser_id')
    //         ->pluck('fundraiser_id');

    //     $totalFundraisers = $unpaidFundraisers->count();

    //     return view('invoice.create', compact('fundraisers', 'latestFundraiser', 'totalFundraisers', 'paidFundraisers', 'alreadyPaidInvoices', 'unpaidFundraisers'));
    // }

}

