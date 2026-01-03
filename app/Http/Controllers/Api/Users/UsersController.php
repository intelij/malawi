<?php

namespace App\Http\Controllers\Api\Users;

use Illuminate\Http\Request;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;
use App\Events\User\Banned;
use App\Events\User\Deleted;
use App\Events\User\UpdatedByAdmin;
use App\Http\Controllers\Api\ApiController;
use App\Http\Filters\UserKeywordSearch;
use App\Http\Requests\User\CreateUserRequest;
use App\Http\Requests\User\UpdateUserRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Repositories\User\UserRepository;
use App\Support\Enum\UserStatus;

class UsersController extends ApiController
{
    public function __construct(private UserRepository $users)
    {
        $this->middleware('permission:users.manage');
    }

    /**
     * Paginate all users.
     */
    public function index(Request $request): \Illuminate\Http\Resources\Json\AnonymousResourceCollection
    {
        $users = QueryBuilder::for(User::class)
            ->allowedIncludes(UserResource::allowedIncludes())
            ->allowedFilters([
                AllowedFilter::custom('search', new UserKeywordSearch),
                AllowedFilter::exact('status'),
            ])
            ->allowedSorts(['id', 'first_name', 'last_name', 'email', 'created_at', 'updated_at'])
            ->defaultSort('id')
            ->paginate($request->per_page ?: 20);

        return UserResource::collection($users);
    }

    public function store(CreateUserRequest $request): UserResource
    {
        $data = $request->only([
            'email', 'password', 'username', 'first_name', 'last_name',
            'phone', 'address', 'country_id', 'birthday', 'role_id',
        ]);

        $data += [
            'status' => setting('approval.enabled') ? UserStatus::WAITING_APPROVAL : UserStatus::ACTIVE,
            'email_verified_at' => $request->verified ? now() : null,
        ];

        $user = $this->users->create($data);

        return new UserResource($user);
    }

    public function show($id): UserResource
    {
        $user = QueryBuilder::for(User::where('id', $id))
            ->allowedIncludes(UserResource::allowedIncludes())
            ->firstOrFail();

        return new UserResource($user);
    }

    public function update(User $user, UpdateUserRequest $request): UserResource
    {
        $data = $request->only([
            'email', 'password', 'username', 'first_name', 'last_name',
            'phone', 'address', 'country_id', 'birthday', 'status', 'role_id',
        ]);

        $user = $this->users->update($user->id, $data);

        event(new UpdatedByAdmin($user));

        // If user status was updated to "Banned",
        // fire the appropriate event.
        if ($this->userIsBanned($user, $request)) {
            event(new Banned($user));
        }

        return new UserResource($user);
    }

    /**
     * Check if user is banned during last update.
     */
    private function userIsBanned(User $user, Request $request): bool
    {
        return $user->status != $request->status && $request->status == UserStatus::BANNED;
    }

    public function destroy(User $user): \Illuminate\Http\JsonResponse
    {
        if ($user->id == auth()->id()) {
            return $this->errorForbidden(__('You cannot delete yourself.'));
        }

        event(new Deleted($user));

        $this->users->delete($user->id);

        return $this->respondWithSuccess();
    }
}
