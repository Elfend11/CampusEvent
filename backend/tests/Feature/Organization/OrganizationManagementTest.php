<?php

namespace Tests\Feature\Organization;

use App\Enums\UserRole;
use App\Models\Organization;
use App\Models\OrganizationMember;
use App\Models\User;
use App\Models\UserRoleAssignment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrganizationManagementTest extends TestCase
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

    private function organizationAdminOf(Organization $organization): User
    {
        $user = $this->userWithRole(UserRole::ORGANIZATION_ADMIN);

        OrganizationMember::create([
            'organization_id' => $organization->id,
            'user_id' => $user->id,
            'role' => OrganizationMember::ROLE_ADMIN,
        ]);

        return $user;
    }

    public function test_super_admin_can_create_organization(): void
    {
        $superAdmin = $this->userWithRole(UserRole::SUPER_ADMIN);

        $response = $this->actingAs($superAdmin)->postJson('/api/v1/organizations', [
            'name' => 'Himpunan Mahasiswa Informatika',
            'type' => 'HMPS',
            'status' => 'ACTIVE',
        ]);

        $response
            ->assertCreated()
            ->assertJsonPath('data.name', 'Himpunan Mahasiswa Informatika')
            ->assertJsonPath('data.members_count', 0);

        $this->assertDatabaseHas('organizations', ['name' => 'Himpunan Mahasiswa Informatika']);
    }

    public function test_organization_admin_cannot_create_organization(): void
    {
        $organization = Organization::factory()->create();
        $admin = $this->organizationAdminOf($organization);

        $this->actingAs($admin)->postJson('/api/v1/organizations', [
            'name' => 'Organisasi Baru',
            'type' => 'UKM',
        ])->assertForbidden();
    }

    public function test_organization_admin_only_sees_assigned_organizations(): void
    {
        $own = Organization::factory()->create(['name' => 'Organisasi Sendiri']);
        $other = Organization::factory()->create(['name' => 'Organisasi Lain']);
        $admin = $this->organizationAdminOf($own);

        $response = $this->actingAs($admin)->getJson('/api/v1/organizations');

        $response->assertOk();
        $names = collect($response->json('data'))->pluck('name');

        $this->assertTrue($names->contains('Organisasi Sendiri'));
        $this->assertFalse($names->contains('Organisasi Lain'));
    }

    public function test_organization_admin_can_update_own_organization(): void
    {
        $organization = Organization::factory()->create(['name' => 'Nama Lama']);
        $admin = $this->organizationAdminOf($organization);

        $this->actingAs($admin)
            ->putJson("/api/v1/organizations/{$organization->id}", ['name' => 'Nama Baru'])
            ->assertOk()
            ->assertJsonPath('data.name', 'Nama Baru');
    }

    public function test_organization_admin_cannot_update_another_organization(): void
    {
        $own = Organization::factory()->create();
        $other = Organization::factory()->create(['name' => 'Tidak Boleh']);
        $admin = $this->organizationAdminOf($own);

        $this->actingAs($admin)
            ->putJson("/api/v1/organizations/{$other->id}", ['name' => 'Diubah'])
            ->assertForbidden();

        $this->assertDatabaseHas('organizations', ['id' => $other->id, 'name' => 'Tidak Boleh']);
    }

    public function test_super_admin_can_add_and_update_members(): void
    {
        $superAdmin = $this->userWithRole(UserRole::SUPER_ADMIN);
        $organization = Organization::factory()->create();
        $member = User::factory()->create();

        $this->actingAs($superAdmin)
            ->postJson("/api/v1/organizations/{$organization->id}/members", [
                'user_id' => $member->id,
                'role' => OrganizationMember::ROLE_MEMBER,
            ])
            ->assertCreated()
            ->assertJsonPath('data.role', 'MEMBER');

        $this->actingAs($superAdmin)
            ->putJson("/api/v1/organizations/{$organization->id}/members/{$member->id}", [
                'role' => OrganizationMember::ROLE_ADMIN,
            ])
            ->assertOk()
            ->assertJsonPath('data.role', 'ADMIN');
    }

    public function test_duplicate_member_is_rejected(): void
    {
        $superAdmin = $this->userWithRole(UserRole::SUPER_ADMIN);
        $organization = Organization::factory()->create();
        $member = User::factory()->create();

        OrganizationMember::create([
            'organization_id' => $organization->id,
            'user_id' => $member->id,
            'role' => OrganizationMember::ROLE_MEMBER,
        ]);

        $this->actingAs($superAdmin)
            ->postJson("/api/v1/organizations/{$organization->id}/members", [
                'user_id' => $member->id,
                'role' => OrganizationMember::ROLE_MEMBER,
            ])
            ->assertStatus(422);
    }

    public function test_organization_admin_can_manage_members_of_own_organization(): void
    {
        $organization = Organization::factory()->create();
        $admin = $this->organizationAdminOf($organization);
        $member = User::factory()->create();

        $this->actingAs($admin)
            ->postJson("/api/v1/organizations/{$organization->id}/members", [
                'user_id' => $member->id,
                'role' => OrganizationMember::ROLE_MEMBER,
            ])
            ->assertCreated();

        $this->actingAs($admin)
            ->deleteJson("/api/v1/organizations/{$organization->id}/members/{$member->id}")
            ->assertNoContent();

        $this->assertDatabaseMissing('organization_members', [
            'organization_id' => $organization->id,
            'user_id' => $member->id,
        ]);
    }

    public function test_student_cannot_access_organization_management(): void
    {
        $student = $this->userWithRole(UserRole::STUDENT);

        $this->actingAs($student)->getJson('/api/v1/organizations')->assertForbidden();
    }

    public function test_organization_name_must_be_unique(): void
    {
        $superAdmin = $this->userWithRole(UserRole::SUPER_ADMIN);
        Organization::factory()->create(['name' => 'Organisasi Unik']);

        $this->actingAs($superAdmin)->postJson('/api/v1/organizations', [
            'name' => 'Organisasi Unik',
            'type' => 'BEM',
        ])->assertStatus(422)->assertJsonValidationErrors('name');
    }
}
