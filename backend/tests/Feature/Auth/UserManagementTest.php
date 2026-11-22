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

    public function test_super_admin_cannot_create_user_through_user_management(): void
    {
        $admin = $this->userWithRole(UserRole::SUPER_ADMIN);

        $response = $this->actingAs($admin)->postJson('/api/v1/users', [
            'name' => 'Akun Dilarang',
            'email' => 'officer@example.test',
            'password' => 'password123',
            'roles' => ['STUDENT'],
        ]);

        $response->assertForbidden();
        $this->assertDatabaseMissing('users', ['email' => 'officer@example.test']);
    }

    public function test_super_admin_cannot_assign_administrator_role_through_user_management(): void
    {
        $admin = $this->userWithRole(UserRole::SUPER_ADMIN);
        $user = $this->userWithRole(UserRole::STUDENT);

        $response = $this->actingAs($admin)->putJson("/api/v1/users/{$user->id}/roles", [
            'roles' => ['ORGANIZATION_ADMIN'],
        ]);

        $response->assertStatus(422)->assertJsonValidationErrors('roles.0');
        $this->assertDatabaseHas('user_roles', [
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
