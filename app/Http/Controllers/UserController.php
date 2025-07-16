<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\UserService;
use App\Http\Resources\UserResource;
use App\Http\Resources\UserResources;
use App\Http\Requests\User\ShowUserRequest;
use App\Http\Requests\User\ShowUsersRequest;
use App\Http\Requests\User\CreateUserRequest;
use App\Http\Requests\User\UpdateUserRequest;
use App\Http\Requests\User\DeleteUserRequest;
use App\Http\Requests\User\DeleteUsersRequest;

class UserController extends Controller
{
    /**
     * @var UserService
     */
    protected $service;

    /**
     * UserController constructor.
     *
     * @param UserService $service
     */
    public function __construct(UserService $service)
    {
        $this->service = $service;
    }

    /**
     * Show users.
     *
     * @param ShowUsersRequest $request
     * @return UserResources|array
     */
    public function showUsers(ShowUsersRequest $request): UserResources|array
    {
        return $this->service->showUsers($request->validated());
    }

    /**
     * Create user.
     *
     * @param CreateUserRequest $request
     * @return array
     */
    public function createUser(CreateUserRequest $request): array
    {
        return $this->service->createUser($request->validated());
    }

    /**
     * Delete multiple users.
     *
     * @param DeleteUsersRequest $request
     * @return array
     */
    public function deleteUsers(DeleteUsersRequest $request): array
    {
        $userIds = request()->input('user_ids', []);
        return $this->service->deleteUsers($userIds);
    }

    /**
     * Show single user.
     *
     * @param ShowUserRequest $request
     * @param User $user
     * @return UserResource
     */
    public function showUser(ShowUserRequest $request, User $user): UserResource
    {
        return $this->service->showUser($user);
    }

    /**
     * Update user.
     *
     * @param UpdateUserRequest $request
     * @param User $user
     * @return array
     */
    public function updateUser(UpdateUserRequest $request, User $user): array
    {
        return $this->service->updateUser($user, $request->validated());
    }

    /**
     * Delete user.
     *
     * @param DeleteUserRequest $request
     * @param User $user
     * @return array
     */
    public function deleteUser(DeleteUserRequest $request, User $user): array
    {
        return $this->service->deleteUser($user);
    }
}
