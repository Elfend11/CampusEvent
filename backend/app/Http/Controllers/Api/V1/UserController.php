<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\StoreUserRequest;
use App\Http\Requests\User\UpdateUserRequest;
use App\Http\Requests\User\UpdateUserRolesRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class UserController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $users = User::query()
            ->with('roles')
            ->when($request->string('search')->trim()->value(), function ($query, $search) {
                $query->where(function ($query) use ($search) {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->orderBy('name')
            ->paginate(20);

        return UserResource::collection($users);
    }

    public function store(StoreUserRequest $request): JsonResponse
    {
        $user = User::create($request->safe()->only(['name', 'email', 'password']));

        $this->syncRoles($user, $request->validated('roles'));

        return response()->json([
            'data' => new UserResource($user->load('roles')),
        ], 201);
    }

    public function show(User $user): JsonResponse
    {
        return response()->json([
            'data' => new UserResource($user->load('roles')),
        ]);
    }

    public function update(UpdateUserRequest $request, User $user): JsonResponse
    {
        $data = $request->safe()->only(['name', 'email', 'password']);

        if (array_key_exists('password', $data) && $data['password'] === null) {
            unset($data['password']);
        }

        $user->update($data);

        return response()->json([
            'data' => new UserResource($user->load('roles')),
        ]);
    }

    public function updateRoles(UpdateUserRolesRequest $request, User $user): JsonResponse
    {
        $this->syncRoles($user, $request->validated('roles'));

        return response()->json([
            'data' => new UserResource($user->load('roles')),
        ]);
    }

    public function destroy(Request $request, User $user): Response
    {
        if ($request->user()->is($user)) {
            abort(422, 'Anda tidak dapat menghapus akun sendiri.');
        }

        $user->delete();

        return response()->noContent();
    }

    /**
     * @param  list<string>  $roles
     */
    private function syncRoles(User $user, array $roles): void
    {
        $roles = array_values(array_unique($roles));

        $user->roles()->whereNotIn('role', $roles)->delete();

        foreach ($roles as $role) {
            $user->roles()->firstOrCreate(['role' => $role]);
        }

        $user->unsetRelation('roles');
    }
}
