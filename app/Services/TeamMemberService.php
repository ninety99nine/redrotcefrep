<?php

namespace App\Services;

use Exception;
use App\Models\User;
use App\Models\Role;
use App\Models\Store;
use App\Models\StoreUser;
use Illuminate\Support\Str;
use App\Enums\EmailVerificationType;
use App\Http\Resources\StoreUserResource;
use App\Http\Resources\StoreUserResources;

class TeamMemberService extends BaseService
{
    protected string|null $modelClassName = 'App\Models\StoreUser';
    protected string|null $resourceClassName = 'App\Http\Resources\StoreUserResource';
    protected string|null $resourceCollectionClassName = 'App\Http\Resources\StoreUserResources';

    /**
     * Show team members.
     *
     * @param array $data
     * @return StoreStoreUserResources|array
     */
    public function showTeamMembers(array $data): StoreUserResources|array
    {
        $storeId = $data['store_id'];

        $query = StoreUser::where('store_id', $storeId);

        $query = $query->when(!request()->has('_sort'), fn($query) => $query->orderBy('joined_at', 'desc'));
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
        $email = $data['email'];
        $roleId = $data['role_id'];
        $storeId = $data['store_id'];
        $firstName = $data['first_name'] ?? null;
        $mobileNumber = $data['mobile_number'] ?? null;

        $store = Store::findOrFail($storeId);
        $user = User::where('email', $email)->first();
        $role = Role::where('store_id', $storeId)->findOrFail($roleId);

        if($user) {
            $storeUser = StoreUser::where('email', $email)->orWhere('user_id', $user->id)->first();
        }else{
            $storeUser = StoreUser::where('email', $email)->first();
        }

        if($storeUser) throw new Exception('This team member has already been invited');

        if (!$user) {

            //  Set a temporary user
            $user = new User([
                'email' => $email,
                'first_name' => $firstName
            ]);

        }

        // Send team member invitation email with verification link
        $authService = new AuthService();
        $authService->sendEmailVerification($user, EmailVerificationType::INVITED_EMAIL, $storeId);

        $storeUser = StoreUser::create([
            'email' => $email,
            'creator' => false,
            'joined_at' => null,
            'invited_at' => now(),
            'role_id' => $role->id,
            'store_id' => $store->id,
            'first_name' => $firstName,
            'user_id' => $user?->id ?? null,
            'mobile_number' => $mobileNumber,
        ]);

        return $this->showCreatedResource($storeUser);
    }

    /**
     * Remove team members.
     *
     * @param array $data
     * @return array
     * @throws Exception
     */
    public function removeTeamMembers(array $data): array
    {
        $teamMemberIds = $data['team_member_ids'];
        $teamMembers = StoreUser::whereIn('id', $teamMemberIds)->get();

        if ($totalTeamMembers = $teamMembers->count()) {
            foreach ($teamMembers as $teamMember) {
                $this->removeTeamMember($teamMember);
            }
            return ['message' => $totalTeamMembers . ($totalTeamMembers == 1 ? ' Team member' : ' Team members') . ' removed'];
        } else {
            throw new Exception('No team members removed');
        }
    }

    /**
     * Show team member.
     *
     * @param StoreUser $teamMember
     * @return StoreUserResource
     */
    public function showTeamMember(StoreUser $teamMember): StoreUserResource
    {
        return $this->showResource($teamMember);
    }

    /**
     * Update team member.
     *
     * @param StoreUser $teamMember
     * @param array $data
     * @return array
     */
    public function updateTeamMember(StoreUser $teamMember, array $data): array
    {
        $roleId = $data['role_id'];

        $teamMember->update(['role_id' => $roleId]);

        return $this->showUpdatedResource($teamMember);
    }

    /**
     * Remove team member.
     *
     * @param StoreUser $teamMember
     * @return array
     * @throws Exception
     */
    public function removeTeamMember(StoreUser $teamMember): array
    {
        if($teamMember->creator) {
            $deleted = false;
        }else{
            $deleted = $teamMember->delete();
        }

        return [
            'deleted' => $deleted,
            'message' => $deleted ? 'Team member deleted' : 'Team member delete unsuccessful'
        ];
    }
}
