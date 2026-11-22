<?php

namespace Tests\Feature\Event;

use App\Enums\EventStatus;
use App\Enums\UserRole;
use App\Models\Event;
use App\Models\EventOfficer;
use App\Models\Organization;
use App\Models\OrganizationMember;
use App\Models\Registration;
use App\Models\User;
use App\Models\UserRoleAssignment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EventManagementTest extends TestCase
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

    /**
     * @return array<string, mixed>
     */
    private function eventPayload(Organization $organization, array $overrides = []): array
    {
        return array_merge([
            'organization_id' => $organization->id,
            'title' => 'Workshop Pengembangan Web',
            'description' => 'Workshop untuk mahasiswa.',
            'location' => 'Lab Komputer',
            'category' => 'Workshop',
            'registration_start' => now()->addDay()->toDateTimeString(),
            'registration_end' => now()->addDays(3)->toDateTimeString(),
            'event_start' => now()->addDays(5)->toDateTimeString(),
            'event_end' => now()->addDays(5)->addHours(3)->toDateTimeString(),
            'capacity' => 50,
            'ticket_price' => 0,
        ], $overrides);
    }

    public function test_public_listing_only_shows_published_events(): void
    {
        $organization = Organization::factory()->create();
        Event::factory()->create([
            'organization_id' => $organization->id,
            'title' => 'Event Publik',
            'status' => EventStatus::PUBLISHED,
        ]);
        Event::factory()->create([
            'organization_id' => $organization->id,
            'title' => 'Event Draft',
            'status' => EventStatus::DRAFT,
        ]);

        $response = $this->getJson('/api/v1/events');

        $response->assertOk();
        $titles = collect($response->json('data'))->pluck('title');

        $this->assertTrue($titles->contains('Event Publik'));
        $this->assertFalse($titles->contains('Event Draft'));
    }

    public function test_public_can_filter_events_by_category_and_search(): void
    {
        $organization = Organization::factory()->create();
        Event::factory()->create([
            'organization_id' => $organization->id,
            'title' => 'Seminar Kecerdasan Buatan',
            'category' => 'Seminar',
            'status' => EventStatus::PUBLISHED,
        ]);
        Event::factory()->create([
            'organization_id' => $organization->id,
            'title' => 'Konser Kampus',
            'category' => 'Konser',
            'status' => EventStatus::PUBLISHED,
        ]);

        $byCategory = $this->getJson('/api/v1/events?category=Seminar');
        $byCategory->assertOk();
        $this->assertCount(1, $byCategory->json('data'));

        $bySearch = $this->getJson('/api/v1/events?search=Konser');
        $bySearch->assertOk();
        $this->assertCount(1, $bySearch->json('data'));
    }

    public function test_organization_admin_can_create_event_for_own_organization(): void
    {
        $organization = Organization::factory()->create();
        $admin = $this->organizationAdminOf($organization);

        $response = $this->actingAs($admin)
            ->postJson('/api/v1/events', $this->eventPayload($organization));

        $response
            ->assertCreated()
            ->assertJsonPath('data.title', 'Workshop Pengembangan Web')
            ->assertJsonPath('data.status', 'DRAFT')
            ->assertJsonPath('data.reserved_count', 0)
            ->assertJsonPath('data.sold_count', 0);
    }

    public function test_organization_admin_cannot_create_event_for_other_organization(): void
    {
        $own = Organization::factory()->create();
        $other = Organization::factory()->create();
        $admin = $this->organizationAdminOf($own);

        $this->actingAs($admin)
            ->postJson('/api/v1/events', $this->eventPayload($other))
            ->assertForbidden();
    }

    public function test_super_admin_can_create_event_for_any_organization(): void
    {
        $superAdmin = $this->userWithRole(UserRole::SUPER_ADMIN);
        $organization = Organization::factory()->create();

        $this->actingAs($superAdmin)
            ->postJson('/api/v1/events', $this->eventPayload($organization))
            ->assertCreated();
    }

    public function test_event_dates_are_validated(): void
    {
        $superAdmin = $this->userWithRole(UserRole::SUPER_ADMIN);
        $organization = Organization::factory()->create();

        $this->actingAs($superAdmin)
            ->postJson('/api/v1/events', $this->eventPayload($organization, [
                'event_start' => now()->addDay()->toDateTimeString(),
            ]))
            ->assertStatus(422)
            ->assertJsonValidationErrors('event_start');
    }

    public function test_event_status_transitions_are_validated(): void
    {
        $superAdmin = $this->userWithRole(UserRole::SUPER_ADMIN);
        $event = Event::factory()->create(['status' => EventStatus::DRAFT]);

        $this->actingAs($superAdmin)
            ->patchJson("/api/v1/events/{$event->id}/status", ['status' => 'CLOSED'])
            ->assertStatus(422);

        $this->actingAs($superAdmin)
            ->patchJson("/api/v1/events/{$event->id}/status", ['status' => 'PUBLISHED'])
            ->assertOk()
            ->assertJsonPath('data.status', 'PUBLISHED');

        $this->actingAs($superAdmin)
            ->patchJson("/api/v1/events/{$event->id}/status", ['status' => 'CLOSED'])
            ->assertOk()
            ->assertJsonPath('data.status', 'CLOSED');
    }

    public function test_capacity_cannot_drop_below_reserved_and_sold(): void
    {
        $superAdmin = $this->userWithRole(UserRole::SUPER_ADMIN);
        $event = Event::factory()->create([
            'capacity' => 10,
            'reserved_count' => 4,
            'sold_count' => 3,
        ]);

        $this->actingAs($superAdmin)
            ->putJson("/api/v1/events/{$event->id}", ['capacity' => 6])
            ->assertStatus(422)
            ->assertJsonValidationErrors('capacity');

        $this->actingAs($superAdmin)
            ->putJson("/api/v1/events/{$event->id}", ['capacity' => 7])
            ->assertOk();
    }

    public function test_organization_admin_cannot_update_another_organizations_event(): void
    {
        $own = Organization::factory()->create();
        $other = Organization::factory()->create();
        $admin = $this->organizationAdminOf($own);
        $event = Event::factory()->create(['organization_id' => $other->id]);

        $this->actingAs($admin)
            ->putJson("/api/v1/events/{$event->id}", ['title' => 'Diubah'])
            ->assertForbidden();
    }

    public function test_guest_cannot_view_draft_event(): void
    {
        $event = Event::factory()->create(['status' => EventStatus::DRAFT]);

        $this->getJson("/api/v1/events/{$event->id}")->assertForbidden();
    }

    public function test_assigned_officer_can_view_draft_event(): void
    {
        $officer = $this->userWithRole(UserRole::CHECK_IN_OFFICER);
        $event = Event::factory()->create(['status' => EventStatus::DRAFT]);
        EventOfficer::create(['event_id' => $event->id, 'user_id' => $officer->id]);

        $this->actingAs($officer)
            ->getJson("/api/v1/events/{$event->id}")
            ->assertOk();
    }

    public function test_public_event_detail_does_not_expose_officer_data(): void
    {
        $event = Event::factory()->create(['status' => EventStatus::PUBLISHED]);
        $officer = $this->userWithRole(UserRole::CHECK_IN_OFFICER);
        $officer->forceFill(['email' => 'petugas.rahasia@example.test'])->save();
        EventOfficer::create(['event_id' => $event->id, 'user_id' => $officer->id]);

        $response = $this->getJson("/api/v1/events/{$event->id}");

        $response->assertOk()->assertJsonMissingPath('data.officers');
        $this->assertStringNotContainsString($officer->email, $response->getContent());
    }

    public function test_event_manager_still_receives_officer_data(): void
    {
        $superAdmin = $this->userWithRole(UserRole::SUPER_ADMIN);
        $event = Event::factory()->create(['status' => EventStatus::PUBLISHED]);
        $officer = $this->userWithRole(UserRole::CHECK_IN_OFFICER);
        EventOfficer::create(['event_id' => $event->id, 'user_id' => $officer->id]);

        $this->actingAs($superAdmin)
            ->getJson("/api/v1/events/{$event->id}")
            ->assertOk()
            ->assertJsonPath('data.officers.0.id', $officer->id)
            ->assertJsonPath('data.officers.0.email', $officer->email);
    }

    public function test_manager_can_assign_and_remove_officers(): void
    {
        $superAdmin = $this->userWithRole(UserRole::SUPER_ADMIN);
        $event = Event::factory()->create();
        $officer = $this->userWithRole(UserRole::CHECK_IN_OFFICER);

        $this->actingAs($superAdmin)
            ->postJson("/api/v1/events/{$event->id}/officers", ['user_id' => $officer->id])
            ->assertCreated();

        $this->actingAs($superAdmin)
            ->postJson("/api/v1/events/{$event->id}/officers", ['user_id' => $officer->id])
            ->assertStatus(422);

        $this->actingAs($superAdmin)
            ->getJson("/api/v1/events/{$event->id}/officers")
            ->assertOk()
            ->assertJsonPath('data.0.id', $officer->id);

        $this->actingAs($superAdmin)
            ->deleteJson("/api/v1/events/{$event->id}/officers/{$officer->id}")
            ->assertNoContent();

        $this->assertDatabaseMissing('event_officers', [
            'event_id' => $event->id,
            'user_id' => $officer->id,
        ]);
    }

    public function test_organization_admin_can_delete_own_empty_event(): void
    {
        $organization = Organization::factory()->create();
        $admin = $this->organizationAdminOf($organization);
        $event = Event::factory()->create(['organization_id' => $organization->id]);

        $this->actingAs($admin)
            ->deleteJson("/api/v1/events/{$event->id}")
            ->assertNoContent();

        $this->assertDatabaseMissing('events', ['id' => $event->id]);
    }

    public function test_event_with_registrations_cannot_be_deleted(): void
    {
        $organization = Organization::factory()->create();
        $admin = $this->organizationAdminOf($organization);
        $event = Event::factory()->create(['organization_id' => $organization->id]);
        Registration::factory()->create(['event_id' => $event->id]);

        $this->actingAs($admin)
            ->deleteJson("/api/v1/events/{$event->id}")
            ->assertStatus(422);

        $this->assertDatabaseHas('events', ['id' => $event->id]);
        $this->assertSame(1, $event->registrations()->count());
    }

    public function test_organization_admin_cannot_delete_another_organizations_event(): void
    {
        $own = Organization::factory()->create();
        $other = Organization::factory()->create();
        $admin = $this->organizationAdminOf($own);
        $event = Event::factory()->create(['organization_id' => $other->id]);

        $this->actingAs($admin)
            ->deleteJson("/api/v1/events/{$event->id}")
            ->assertForbidden();

        $this->assertDatabaseHas('events', ['id' => $event->id]);
    }

    public function test_student_cannot_delete_event(): void
    {
        $student = $this->userWithRole(UserRole::STUDENT);
        $event = Event::factory()->create();

        $this->actingAs($student)
            ->deleteJson("/api/v1/events/{$event->id}")
            ->assertForbidden();

        $this->assertDatabaseHas('events', ['id' => $event->id]);
    }

    public function test_super_admin_can_delete_empty_event(): void
    {
        $superAdmin = $this->userWithRole(UserRole::SUPER_ADMIN);
        $event = Event::factory()->create();

        $this->actingAs($superAdmin)
            ->deleteJson("/api/v1/events/{$event->id}")
            ->assertNoContent();

        $this->assertDatabaseMissing('events', ['id' => $event->id]);
    }
}
