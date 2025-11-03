<?php

namespace App\Services;

use App\Enums\Association;
use Exception;
use App\Models\User;
use App\Http\Resources\UserResource;
use App\Http\Resources\UserResources;

class UserService extends BaseService
{
    /**
     * Show users.
     *
     * @param array $data
     * @return UserResources|array
     */
    public function showUsers(array $data): UserResources|array
    {
        $storeId = $data['store_id'] ?? null;
        $association = isset($data['association']) ? Association::tryFrom($data['association']) : null;

        if($association == Association::SUPER_ADMIN) {

            $query = User::query();

        }else {

            $query = User::whereHas('stores', function ($query) use ($storeId) {
                $query->where('store_user.store_id', $storeId);
            });

        }

        $query = User::query()->when(!request()->has('_sort'), fn($query) => $query->latest());
        return $this->setQuery($query)->getOutput();
    }

    /**
     * Create user.
     *
     * @param array $data
     * @return array
     * @throws Exception
     */
    public function createUser(array $data): array
    {
        $user = User::create($data);
        return $this->showCreatedResource($user);
    }

    /**
     * Delete users.
     *
     * @param array $userIds
     * @return array
     * @throws Exception
     */
    public function deleteUsers(array $userIds): array
    {
        $users = User::whereIn('id', $userIds)->get();

        if ($totalUsers = $users->count()) {

            foreach ($users as $user) {

                $this->deleteUser($user);

            }

            return ['message' => $totalUsers . ($totalUsers == 1 ? ' User' : ' Users') . ' deleted'];
        } else {
            throw new Exception('No Users deleted');
        }
    }

    /**
     * Show user.
     *
     * @param User $user
     * @return UserResource
     */
    public function showUser(User $user): UserResource
    {
        return $this->showResource($user);
    }

    /**
     * Update user.
     *
     * @param User $user
     * @param array $data
     * @return array
     */
    public function updateUser(User $user, array $data): array
    {
        $user->update($data);
        return $this->showUpdatedResource($user);
    }

    /**
     * Delete user.
     *
     * @param User $user
     * @return array
     * @throws Exception
     */
    public function deleteUser(User $user): array
    {
        $deleted = $user->delete();

        return [
            'deleted' => $deleted,
            'message' => $deleted ? 'User deleted' : 'User delete unsuccessful'
        ];
    }
}
