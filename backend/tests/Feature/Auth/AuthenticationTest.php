<?php

namespace Tests\Feature\Auth;

use App\Enums\UserRole;
use App\Models\User;
use App\Models\UserRoleAssignment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withHeader('Origin', 'http://localhost:8000');
    }

    private function userWithRole(UserRole $role, array $attributes = []): User
    {
        $user = User::factory()->create($attributes);
        UserRoleAssignment::create(['user_id' => $user->id, 'role' => $role->value]);

        return $user;
    }

    public function test_user_can_login_with_valid_credentials(): void
    {
        $user = $this->userWithRole(UserRole::STUDENT, [
            'email' => 'student@example.test',
            'password' => 'password',
        ]);

        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'student@example.test',
            'password' => 'password',
        ]);

        $response
            ->assertOk()
            ->assertJsonPath('data.email', $user->email)
            ->assertJsonPath('data.roles', ['STUDENT']);

        $this->assertAuthenticatedAs($user);
    }

    public function test_login_fails_with_invalid_credentials(): void
    {
        User::factory()->create(['email' => 'student@example.test']);

        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'student@example.test',
            'password' => 'wrong-password',
        ]);

        $response->assertStatus(422)->assertJsonValidationErrors('email');
        $this->assertGuest();
    }

    public function test_authenticated_user_can_fetch_own_profile(): void
    {
        $user = $this->userWithRole(UserRole::STUDENT);

        $response = $this->actingAs($user)->getJson('/api/v1/auth/user');

        $response
            ->assertOk()
            ->assertJsonPath('data.id', $user->id)
            ->assertJsonPath('data.roles', ['STUDENT']);
    }

    public function test_guest_cannot_fetch_profile(): void
    {
        $this->getJson('/api/v1/auth/user')->assertUnauthorized();
    }

    public function test_user_can_logout(): void
    {
        $user = $this->userWithRole(UserRole::STUDENT);

        $this->actingAs($user)->postJson('/api/v1/auth/logout')->assertNoContent();
        $this->assertGuest('web');
    }

    public function test_multi_role_account_returns_all_roles(): void
    {
        $user = $this->userWithRole(UserRole::STUDENT, ['email' => 'multi@example.test']);
        UserRoleAssignment::create(['user_id' => $user->id, 'role' => UserRole::CHECK_IN_OFFICER->value]);

        $response = $this->actingAs($user)->getJson('/api/v1/auth/user');

        $response->assertOk();
        $this->assertEqualsCanonicalizing(
            ['STUDENT', 'CHECK_IN_OFFICER'],
            $response->json('data.roles'),
        );
    }
}
