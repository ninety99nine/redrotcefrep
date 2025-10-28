<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\TeamMemberService;
use App\Http\Resources\UserResource;
use App\Http\Resources\UserResources;
use App\Http\Requests\TeamMember\AddTeamMemberRequest;
use App\Http\Requests\TeamMember\ShowTeamMemberRequest;
use App\Http\Requests\TeamMember\ShowTeamMembersRequest;
use App\Http\Requests\TeamMember\UpdateTeamMemberRequest;
use App\Http\Requests\TeamMember\RemoveTeamMemberRequest;
use App\Http\Requests\TeamMember\RemoveTeamMembersRequest;

class TeamMemberController extends Controller
{
    /**
     * @var TeamMemberService
     */
    protected $service;

    /**
     * TeamMemberController constructor.
     *
     * @param TeamMemberService $service
     */
    public function __construct(TeamMemberService $service)
    {
        $this->service = $service;
    }

    /**
     * Show team members.
     *
     * @param ShowTeamMembersRequest $request
     * @return UserResources|array
     */
    public function showTeamMembers(ShowTeamMembersRequest $request): UserResources|array
    {
        return $this->service->showTeamMembers($request->validated());
    }

    /**
     * Add team member.
     *
     * @param AddTeamMemberRequest $request
     * @return array
     */
    public function addTeamMember(AddTeamMemberRequest $request): array
    {
        return $this->service->addTeamMember($request->validated());
    }

    /**
     * Remove multiple team members.
     *
     * @param RemoveTeamMembersRequest $request
     * @return array
     */
    public function removeTeamMembers(RemoveTeamMembersRequest $request): array
    {
        $userIds = request()->input('user_ids', []);
        return $this->service->removeTeamMembers($userIds);
    }

    /**
     * Show team member.
     *
     * @param ShowTeamMemberRequest $request
     * @param User $user
     * @return UserResource
     */
    public function showTeamMember(ShowTeamMemberRequest $request, User $user): UserResource
    {
        return $this->service->showTeamMember($user);
    }

    /**
     * Update team member.
     *
     * @param UpdateTeamMemberRequest $request
     * @param User $user
     * @return array
     */
    public function updateTeamMember(UpdateTeamMemberRequest $request, User $user): array
    {
        return $this->service->updateTeamMember($user, $request->validated());
    }

    /**
     * Remove team member.
     *
     * @param RemoveTeamMemberRequest $request
     * @param User $user
     * @return array
     */
    public function removeTeamMember(RemoveTeamMemberRequest $request, User $user): array
    {
        return $this->service->removeTeamMember($user);
    }
}
