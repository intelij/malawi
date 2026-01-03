<?php

namespace Vanguard\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
// use Vanguard\Http\Controllers\Controller;
use App\Services\MembershipNumberGenerator;

class MembershipController extends Controller
{
    public function store()
    {
        $prefix = 'MAL'; // Example prefix
        $membershipNumber = MembershipNumberGenerator::generateNumber($prefix);

        // Use the generated membership number as needed
        // For example, save it to the database or return it as part of the response
    }
}
