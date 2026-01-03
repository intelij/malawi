<?php

namespace App\Http\Controllers\Api\Auth;

use Auth;
use Exception;
use Illuminate\Http\JsonResponse;
use Socialite;
use App\Events\User\LoggedIn;
use App\Http\Controllers\Api\ApiController;
use App\Http\Requests\Auth\Social\ApiAuthenticateRequest;
use App\Repositories\User\UserRepository;
use App\Services\Auth\Social\SocialManager;

class SocialLoginController extends ApiController
{
    public function __construct(private readonly UserRepository $users, private readonly SocialManager $socialManager)
    {
    }

    public function index(ApiAuthenticateRequest $request): JsonResponse
    {
        try {
            $socialUser = Socialite::driver($request->network)->userFromToken($request->social_token);
        } catch (Exception $e) {
            return $this->errorInternalError('Could not connect to specified social network.');
        }

        $user = $this->users->findBySocialId(
            $request->network,
            $socialUser->getId()
        );

        if (! $user) {
            if (! setting('reg_enabled')) {
                return $this->errorForbidden('Only users who already created an account can log in.');
            }

            $user = $this->socialManager->associate($socialUser, $request->network);
        }

        if ($user->isBanned()) {
            return $this->errorForbidden(__('Your account is banned by administrators.'));
        }

        if ($user->isWaitingApproval()) {
            return $this->errorForbidden(__('Your account is waiting approval from administrators.'));
        }

        Auth::setUser($user);

        event(new LoggedIn);

        return $this->respondWithArray([
            'token' => $user->createToken($request->device_name)->plainTextToken,
        ]);
    }
}
