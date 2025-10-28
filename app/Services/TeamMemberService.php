<?php

namespace App\Services;

use Exception;
use App\Models\User;
use App\Models\Store;
use Illuminate\Support\Str;
use App\Enums\EmailVerificationType;
use App\Http\Resources\UserResource;
use App\Http\Resources\UserResources;

class TeamMemberService extends BaseService
{
    protected string|null $modelClassName = 'App\Models\User';
    protected string|null $resourceClassName = 'App\Http\Resources\UserResource';
    protected string|null $resourceCollectionClassName = 'App\Http\Resources\UserResources';

    /**
     * Show team members.
     *
     * @param array $data
     * @return UserResources|array
     */
    public function showTeamMembers(array $data): UserResources|array
    {
        $storeId = $data['store_id'] ?? null;
        $query = User::whereHas('stores', fn($q) => $q->where('store_id', $storeId));
        return $this->setQuery($query)->getOutput();
    }

    /**
     * Add team member.
     *
     * @param array $data
     * @return array
     * @throws Exception
     */
    public function addTeamMember(array $data): array
    {
        $store = Store::findOrFail($data['store_id']);
        $roleId = $store->roles()->first()->id;
        dd($roleId);

        $email = $data['email'];
        $roleId = $data['role_id'];
        $storeId = $data['store_id'];

        $user = User::where('email', $email)->first();

        if (!$user) {
            $user = User::create([
                'id' => Str::uuid(),
                'email' => $email,
                'first_name' => $data['first_name'] ?? '',
                'last_name' => $data['last_name'] ?? '',
                'email_verified_at' => null,
            ]);

            // Send team member invitation email with verification link
            $authService = new AuthService();
            $authService->sendEmailVerification($user, EmailVerificationType::INVITED_EMAIL, $storeId);
        }

        $store = Store::findOrFail($storeId);

        $roleId = $store->roles()->first()->id;
        $store->users()->syncWithoutDetaching([$user->id => ['role_id' => $roleId]]);

        return $this->showCreatedResource($user);
    }

    /**
     * Remove team members.
     *
     * @param array $userIds
     * @return array
     * @throws Exception
     */
    public function removeTeamMembers(array $userIds): array
    {
        $storeId = request()->input('store_id');
        $store = Store::findOrFail($storeId);
        $users = $store->users()->whereIn('users.id', $userIds)->get();

        if ($totalUsers = $users->count()) {
            foreach ($users as $user) {
                $this->removeTeamMember($user);
            }
            return ['message' => $totalUsers . ($totalUsers == 1 ? ' Team member' : ' Team members') . ' removed'];
        } else {
            throw new Exception('No team members removed');
        }
    }

    /**
     * Show team member.
     *
     * @param User $user
     * @return UserResource
     */
    public function showTeamMember(User $user): UserResource
    {
        return $this->showResource($user);
    }

    /**
     * Update team member.
     *
     * @param User $user
     * @param array $data
     * @return array
     */
    public function updateTeamMember(User $user, array $data): array
    {
        $storeId = $data['store_id'];
        $roleId = $data['role_id'];

        $store = Store::findOrFail($storeId);
        $store->users()->syncWithoutDetaching([$user->id => ['role_id' => $roleId]]);

        return $this->showUpdatedResource($user);
    }

    /**
     * Remove team member.
     *
     * @param User $user
     * @return array
     * @throws Exception
     */
    public function removeTeamMember(User $user): array
    {
        $storeId = request()->input('store_id');
        $store = Store::findOrFail($storeId);
        $detached = $store->users()->detach($user->id);

        if ($detached) {
            return ['message' => 'Team member removed'];
        } else {
            throw new Exception('Team member removal unsuccessful');
        }
    }
}
