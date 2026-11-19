<?php

namespace Tests\Feature\Auth;

use App\Enums\UserRole;
use App\Models\User;
use App\Models\UserRoleAssignment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withHeader('Origin', 'http://localhost:8000');
    }

    private function userWithRole(UserRole $role): User
    {
        $user = User::factory()->create();
        UserRoleAssignment::create(['user_id' => $user->id, 'role' => $role->value]);

        return $user;
    }

    public function test_super_admin_can_list_users(): void
    {
        $admin = $this->userWithRole(UserRole::SUPER_ADMIN);
        User::factory()->count(3)->create();

        $response = $this->actingAs($admin)->getJson('/api/v1/users');

        $response->assertOk();
        $this->assertGreaterThanOrEqual(4, count($response->json('data')));
    }

    public function test_super_admin_can_create_user_with_multiple_roles(): void
    {
        $admin = $this->userWithRole(UserRole::SUPER_ADMIN);

        $response = $this->actingAs($admin)->postJson('/api/v1/users', [
            'name' => 'Petugas Ganda',
            'email' => 'officer@example.test',
            'password' => 'password123',
            'roles' => ['STUDENT', 'CHECK_IN_OFFICER'],
        ]);

        $response->assertCreated()->assertJsonPath('data.email', 'officer@example.test');

        $this->assertEqualsCanonicalizing(
            ['STUDENT', 'CHECK_IN_OFFICER'],
            $response->json('data.roles'),
        );
    }

    public function test_super_admin_can_replace_user_roles(): void
    {
        $admin = $this->userWithRole(UserRole::SUPER_ADMIN);
        $user = $this->userWithRole(UserRole::STUDENT);

        $response = $this->actingAs($admin)->putJson("/api/v1/users/{$user->id}/roles", [
            'roles' => ['ORGANIZATION_ADMIN'],
        ]);

        $response->assertOk()->assertJsonPath('data.roles', ['ORGANIZATION_ADMIN']);

        $this->assertDatabaseMissing('user_roles', [
            'user_id' => $user->id,
            'role' => 'STUDENT',
        ]);
    }

    public function test_student_cannot_manage_users(): void
    {
        $student = $this->userWithRole(UserRole::STUDENT);

        $this->actingAs($student)->getJson('/api/v1/users')->assertForbidden();
        $this->actingAs($student)->postJson('/api/v1/users', [])->assertForbidden();
    }

    public function test_organization_admin_cannot_manage_platform_users(): void
    {
        $organizationAdmin = $this->userWithRole(UserRole::ORGANIZATION_ADMIN);

        $this->actingAs($organizationAdmin)->getJson('/api/v1/users')->assertForbidden();
    }

    public function test_user_creation_requires_valid_roles(): void
    {
        $admin = $this->userWithRole(UserRole::SUPER_ADMIN);

        $response = $this->actingAs($admin)->postJson('/api/v1/users', [
            'name' => 'Invalid Role',
            'email' => 'invalid@example.test',
            'password' => 'password123',
            'roles' => ['NOT_A_ROLE'],
        ]);

        $response->assertStatus(422)->assertJsonValidationErrors('roles.0');
    }

    public function test_super_admin_cannot_delete_own_account(): void
    {
        $admin = $this->userWithRole(UserRole::SUPER_ADMIN);

        $this->actingAs($admin)
            ->deleteJson("/api/v1/users/{$admin->id}")
            ->assertStatus(422);

        $this->assertDatabaseHas('users', ['id' => $admin->id]);
    }
}
