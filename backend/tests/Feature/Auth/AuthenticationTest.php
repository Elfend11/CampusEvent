<?php

namespace Tests\Feature\Auth;

use App\Enums\UserRole;
use App\Models\User;
use App\Models\UserRoleAssignment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
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

    public function test_student_can_register_account(): void
    {
        $response = $this->postJson('/api/v1/auth/register', [
            'name' => 'Mahasiswa Baru',
            'email' => 'mahasiswa.baru@example.test',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
        ]);

        $response
            ->assertCreated()
            ->assertJsonPath('data.name', 'Mahasiswa Baru')
            ->assertJsonPath('data.email', 'mahasiswa.baru@example.test')
            ->assertJsonPath('data.roles', [UserRole::STUDENT->value]);

        $user = User::where('email', 'mahasiswa.baru@example.test')->firstOrFail();

        $this->assertAuthenticatedAs($user);
        $this->assertTrue(Hash::check('Password123!', $user->password));
        $this->assertNotSame('Password123!', $user->password);
        $this->assertDatabaseHas('user_roles', [
            'user_id' => $user->id,
            'role' => UserRole::STUDENT->value,
        ]);
    }

    public function test_registration_rejects_duplicate_email(): void
    {
        User::factory()->create(['email' => 'sudah.ada@example.test']);

        $this->postJson('/api/v1/auth/register', [
            'name' => 'Mahasiswa Ganda',
            'email' => 'sudah.ada@example.test',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
        ])
            ->assertStatus(422)
            ->assertJsonValidationErrors('email');

        $this->assertSame(1, User::where('email', 'sudah.ada@example.test')->count());
    }

    public function test_registration_validates_password_confirmation(): void
    {
        $this->postJson('/api/v1/auth/register', [
            'name' => 'Mahasiswa Salah',
            'email' => 'salah@example.test',
            'password' => 'Password123!',
            'password_confirmation' => 'Password456!',
        ])
            ->assertStatus(422)
            ->assertJsonValidationErrors('password');

        $this->assertDatabaseMissing('users', ['email' => 'salah@example.test']);
    }

    public function test_registration_ignores_roles_sent_by_frontend(): void
    {
        $response = $this->postJson('/api/v1/auth/register', [
            'name' => 'Mahasiswa Nakal',
            'email' => 'nakal@example.test',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
            'roles' => [UserRole::SUPER_ADMIN->value],
        ]);

        $response
            ->assertCreated()
            ->assertJsonPath('data.roles', [UserRole::STUDENT->value]);

        $user = User::where('email', 'nakal@example.test')->firstOrFail();
        $this->assertFalse($user->hasRole(UserRole::SUPER_ADMIN));
        $this->assertTrue($user->hasRole(UserRole::STUDENT));
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
