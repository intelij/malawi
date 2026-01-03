<?php

namespace App\Http\Controllers\Web\Profile;

use Illuminate\Http\RedirectResponse;
use App\Events\User\UpdatedProfileDetails;
use App\Http\Controllers\Controller;
use App\Http\Requests\User\UpdateProfileDetailsRequest;
use App\Repositories\User\UserRepository;

class DetailsController extends Controller
{
    public function __construct(private readonly UserRepository $users)
    {
    }

    public function update(UpdateProfileDetailsRequest $request): RedirectResponse
    {
        $this->users->update(auth()->id(), $request->except('role_id', 'status'));

        event(new UpdatedProfileDetails);

        return redirect()->back()
            ->withSuccess(__('Profile updated successfully.'));
    }
}
