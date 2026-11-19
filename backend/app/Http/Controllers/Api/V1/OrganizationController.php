<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\Organization\StoreOrganizationMemberRequest;
use App\Http\Requests\Organization\StoreOrganizationRequest;
use App\Http\Requests\Organization\UpdateOrganizationMemberRequest;
use App\Http\Requests\Organization\UpdateOrganizationRequest;
use App\Http\Resources\OrganizationMemberResource;
use App\Http\Resources\OrganizationResource;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class OrganizationController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $this->authorize('viewAny', Organization::class);

        $organizations = Organization::query()
            ->withCount('members')
            ->when(
                ! $request->user()->hasRole(UserRole::SUPER_ADMIN),
                fn ($query) => $query->whereHas(
                    'members',
                    fn ($query) => $query->where('user_id', $request->user()->id),
                ),
            )
            ->orderBy('name')
            ->paginate(20);

        return OrganizationResource::collection($organizations);
    }

    public function store(StoreOrganizationRequest $request): JsonResponse
    {
        $this->authorize('create', Organization::class);

        $organization = Organization::create($request->validated());

        return response()->json([
            'data' => new OrganizationResource($organization->loadCount('members')),
        ], 201);
    }

    public function show(Organization $organization): JsonResponse
    {
        $this->authorize('view', $organization);

        return response()->json([
            'data' => new OrganizationResource(
                $organization->loadCount('members')->load('members.user.roles'),
            ),
        ]);
    }

    public function update(UpdateOrganizationRequest $request, Organization $organization): JsonResponse
    {
        $this->authorize('update', $organization);

        $organization->update($request->validated());

        return response()->json([
            'data' => new OrganizationResource($organization->loadCount('members')),
        ]);
    }

    public function members(Organization $organization): AnonymousResourceCollection
    {
        $this->authorize('manageMembers', $organization);

        return OrganizationMemberResource::collection(
            $organization->members()->with('user.roles')->orderBy('id')->get(),
        );
    }

    public function addMember(StoreOrganizationMemberRequest $request, Organization $organization): JsonResponse
    {
        $this->authorize('manageMembers', $organization);

        $member = $organization->members()->firstOrCreate(
            ['user_id' => $request->validated('user_id')],
            ['role' => $request->validated('role')],
        );

        if (! $member->wasRecentlyCreated) {
            return response()->json([
                'message' => 'Pengguna sudah menjadi anggota organisasi ini.',
            ], 422);
        }

        return response()->json([
            'data' => new OrganizationMemberResource($member->load('user.roles')),
        ], 201);
    }

    public function updateMember(
        UpdateOrganizationMemberRequest $request,
        Organization $organization,
        User $user,
    ): JsonResponse {
        $this->authorize('manageMembers', $organization);

        $member = $organization->members()->where('user_id', $user->id)->firstOrFail();
        $member->update(['role' => $request->validated('role')]);

        return response()->json([
            'data' => new OrganizationMemberResource($member->load('user.roles')),
        ]);
    }

    public function removeMember(Organization $organization, User $user): Response
    {
        $this->authorize('manageMembers', $organization);

        $organization->members()->where('user_id', $user->id)->delete();

        return response()->noContent();
    }
}
