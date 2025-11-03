<?php

namespace App\Http\Controllers;

use App\Models\StoreUser;
use App\Services\TeamMemberService;
use App\Http\Resources\StoreUserResource;
use App\Http\Resources\StoreUserResources;
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
     * @return StoreUserResources|array
     */
    public function showTeamMembers(ShowTeamMembersRequest $request): StoreUserResources|array
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
        return $this->service->removeTeamMembers($request->validated());
    }

    /**
     * Show team member.
     *
     * @param ShowTeamMemberRequest $request
     * @param StoreUser $teamMember
     * @return StoreUserResource
     */
    public function showTeamMember(ShowTeamMemberRequest $request, StoreUser $teamMember): StoreUserResource
    {
        return $this->service->showTeamMember($teamMember);
    }

    /**
     * Update team member.
     *
     * @param UpdateTeamMemberRequest $request
     * @param StoreUser $teamMember
     * @return array
     */
    public function updateTeamMember(UpdateTeamMemberRequest $request, StoreUser $teamMember): array
    {
        return $this->service->updateTeamMember($teamMember, $request->validated());
    }

    /**
     * Remove team member.
     *
     * @param RemoveTeamMemberRequest $request
     * @param StoreUser $teamMember
     * @return array
     */
    public function removeTeamMember(RemoveTeamMemberRequest $request, StoreUser $teamMember): array
    {
        return $this->service->removeTeamMember($teamMember);
    }
}
