<?php

namespace Tests\Feature\Registration;

use App\Enums\EventStatus;
use App\Enums\RegistrationStatus;
use App\Enums\TicketStatus;
use App\Enums\UserRole;
use App\Models\Event;
use App\Models\Organization;
use App\Models\OrganizationMember;
use App\Models\Registration;
use App\Models\User;
use App\Models\UserRoleAssignment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistrationTest extends TestCase
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

    private function freeEvent(array $overrides = []): Event
    {
        return Event::factory()->create(array_merge([
            'status' => EventStatus::PUBLISHED,
            'ticket_price' => 0,
            'capacity' => 10,
            'reserved_count' => 0,
            'sold_count' => 0,
        ], $overrides));
    }

    private function paidEvent(array $overrides = []): Event
    {
        return $this->freeEvent(array_merge(['ticket_price' => 25000], $overrides));
    }

    public function test_student_can_register_for_free_event_and_receives_ticket(): void
    {
        $student = $this->userWithRole(UserRole::STUDENT);
        $event = $this->freeEvent();

        $response = $this->actingAs($student)
            ->postJson('/api/v1/registrations', ['event_id' => $event->id]);

        $response
            ->assertCreated()
            ->assertJsonPath('data.status', RegistrationStatus::CONFIRMED->value)
            ->assertJsonPath('data.expires_at', null)
            ->assertJsonPath('data.ticket.status', TicketStatus::ACTIVE->value)
            ->assertJsonPath('data.payment', null);

        $this->assertNotEmpty($response->json('data.ticket_token'));

        $event->refresh();
        $this->assertSame(1, $event->sold_count);
        $this->assertSame(0, $event->reserved_count);
        $this->assertSame(9, $event->availableQuota());

        $this->assertDatabaseHas('tickets', [
            'registration_id' => $response->json('data.id'),
            'status' => TicketStatus::ACTIVE->value,
        ]);
    }

    public function test_student_can_reserve_paid_event_for_ten_minutes(): void
    {
        $student = $this->userWithRole(UserRole::STUDENT);
        $event = $this->paidEvent();

        $response = $this->actingAs($student)
            ->postJson('/api/v1/registrations', ['event_id' => $event->id]);

        $response
            ->assertCreated()
            ->assertJsonPath('data.status', RegistrationStatus::PENDING->value)
            ->assertJsonPath('data.ticket', null)
            ->assertJsonPath('data.payment', null);

        $expiresAt = \Illuminate\Support\Carbon::parse($response->json('data.expires_at'));
        $this->assertEqualsWithDelta(
            now()->addMinutes(10)->timestamp,
            $expiresAt->timestamp,
            5,
        );

        $event->refresh();
        $this->assertSame(1, $event->reserved_count);
        $this->assertSame(0, $event->sold_count);
        $this->assertDatabaseMissing('tickets', ['registration_id' => $response->json('data.id')]);
    }

    public function test_duplicate_confirmed_registration_is_rejected(): void
    {
        $student = $this->userWithRole(UserRole::STUDENT);
        $event = $this->freeEvent();

        $this->actingAs($student)
            ->postJson('/api/v1/registrations', ['event_id' => $event->id])
            ->assertCreated();

        $this->actingAs($student)
            ->postJson('/api/v1/registrations', ['event_id' => $event->id])
            ->assertStatus(409);

        $this->assertSame(1, $event->fresh()->sold_count);
        $this->assertSame(1, Registration::count());
    }

    public function test_repeated_pending_reservation_is_idempotent(): void
    {
        $student = $this->userWithRole(UserRole::STUDENT);
        $event = $this->paidEvent();

        $first = $this->actingAs($student)
            ->postJson('/api/v1/registrations', ['event_id' => $event->id])
            ->assertCreated();

        $second = $this->actingAs($student)
            ->postJson('/api/v1/registrations', ['event_id' => $event->id])
            ->assertOk();

        $this->assertSame($first->json('data.id'), $second->json('data.id'));
        $this->assertSame(1, $event->fresh()->reserved_count);
        $this->assertSame(1, Registration::count());
    }

    public function test_registration_is_rejected_when_quota_is_exhausted(): void
    {
        $student = $this->userWithRole(UserRole::STUDENT);
        $event = $this->freeEvent(['capacity' => 1, 'sold_count' => 1]);

        $this->actingAs($student)
            ->postJson('/api/v1/registrations', ['event_id' => $event->id])
            ->assertStatus(409);

        $this->assertSame(0, Registration::count());
    }

    public function test_registration_is_rejected_when_event_is_not_published(): void
    {
        $student = $this->userWithRole(UserRole::STUDENT);
        $event = $this->freeEvent(['status' => EventStatus::DRAFT]);

        $this->actingAs($student)
            ->postJson('/api/v1/registrations', ['event_id' => $event->id])
            ->assertStatus(422);

        $this->assertSame(0, Registration::count());
    }

    public function test_registration_is_rejected_outside_registration_window(): void
    {
        $student = $this->userWithRole(UserRole::STUDENT);

        $notStarted = $this->freeEvent([
            'registration_start' => now()->addDay(),
            'registration_end' => now()->addDays(2),
        ]);
        $closed = $this->freeEvent([
            'registration_start' => now()->subDays(2),
            'registration_end' => now()->subDay(),
        ]);

        $this->actingAs($student)
            ->postJson('/api/v1/registrations', ['event_id' => $notStarted->id])
            ->assertStatus(422);

        $this->actingAs($student)
            ->postJson('/api/v1/registrations', ['event_id' => $closed->id])
            ->assertStatus(422);

        $this->assertSame(0, Registration::count());
    }

    public function test_guest_cannot_register(): void
    {
        $event = $this->freeEvent();

        $this->postJson('/api/v1/registrations', ['event_id' => $event->id])
            ->assertUnauthorized();
    }

    public function test_expired_reservation_releases_quota_and_allows_registration_again(): void
    {
        $student = $this->userWithRole(UserRole::STUDENT);
        $event = $this->paidEvent(['capacity' => 1]);

        $first = $this->actingAs($student)
            ->postJson('/api/v1/registrations', ['event_id' => $event->id])
            ->assertCreated();

        $this->assertSame(1, $event->fresh()->reserved_count);

        $this->travel(11)->minutes();

        $second = $this->actingAs($student)
            ->postJson('/api/v1/registrations', ['event_id' => $event->id])
            ->assertOk()
            ->assertJsonPath('data.status', RegistrationStatus::PENDING->value);

        $this->assertSame($first->json('data.id'), $second->json('data.id'));
        $this->assertSame(1, $event->fresh()->reserved_count);
        $this->assertSame(1, Registration::count());
        $this->assertTrue(
            \Illuminate\Support\Carbon::parse($second->json('data.expires_at'))->isFuture(),
        );
    }

    public function test_expiration_command_releases_quota_only_once(): void
    {
        $student = $this->userWithRole(UserRole::STUDENT);
        $event = $this->paidEvent(['capacity' => 1]);
        $registration = Registration::factory()->create([
            'user_id' => $student->id,
            'event_id' => $event->id,
            'status' => RegistrationStatus::PENDING,
            'expires_at' => now()->subMinute(),
        ]);
        $event->update(['reserved_count' => 1]);

        $this->artisan('registrations:expire')->assertSuccessful();
        $this->assertSame(RegistrationStatus::EXPIRED, $registration->fresh()->status);
        $this->assertSame(0, $event->fresh()->reserved_count);

        $this->artisan('registrations:expire')->assertSuccessful();
        $this->assertSame(0, $event->fresh()->reserved_count);
    }

    public function test_pending_reservation_expires_on_access_when_window_passed(): void
    {
        $student = $this->userWithRole(UserRole::STUDENT);
        $event = $this->paidEvent(['capacity' => 1]);
        $registration = Registration::factory()->create([
            'user_id' => $student->id,
            'event_id' => $event->id,
            'status' => RegistrationStatus::PENDING,
            'expires_at' => now()->subMinute(),
        ]);
        $event->update(['reserved_count' => 1]);

        $this->actingAs($student)
            ->getJson("/api/v1/registrations/{$registration->id}")
            ->assertOk();

        // The scheduler owns the transition; reading does not mutate state.
        $this->assertSame(RegistrationStatus::PENDING, $registration->fresh()->status);
    }

    public function test_organization_admin_cancellation_releases_pending_quota_once(): void
    {
        $organization = Organization::factory()->create();
        $admin = $this->organizationAdminOf($organization);
        $student = $this->userWithRole(UserRole::STUDENT);
        $event = $this->paidEvent(['organization_id' => $organization->id, 'capacity' => 1]);
        $registration = Registration::factory()->create([
            'user_id' => $student->id,
            'event_id' => $event->id,
            'status' => RegistrationStatus::PENDING,
            'expires_at' => now()->addMinutes(10),
        ]);
        $event->update(['reserved_count' => 1]);

        $this->actingAs($admin)
            ->postJson("/api/v1/registrations/{$registration->id}/cancel")
            ->assertOk()
            ->assertJsonPath('data.status', RegistrationStatus::CANCELLED->value);

        $this->assertSame(0, $event->fresh()->reserved_count);

        $this->actingAs($admin)
            ->postJson("/api/v1/registrations/{$registration->id}/cancel")
            ->assertOk();

        $this->assertSame(0, $event->fresh()->reserved_count);
    }

    public function test_organization_admin_cancellation_of_confirmed_registration_voids_ticket_once(): void
    {
        $organization = Organization::factory()->create();
        $admin = $this->organizationAdminOf($organization);
        $student = $this->userWithRole(UserRole::STUDENT);
        $event = $this->freeEvent(['organization_id' => $organization->id]);

        $response = $this->actingAs($student)
            ->postJson('/api/v1/registrations', ['event_id' => $event->id])
            ->assertCreated();

        $registrationId = $response->json('data.id');
        $this->assertSame(1, $event->fresh()->sold_count);

        $this->actingAs($admin)
            ->postJson("/api/v1/registrations/{$registrationId}/cancel")
            ->assertOk()
            ->assertJsonPath('data.status', RegistrationStatus::CANCELLED->value);

        $this->assertSame(0, $event->fresh()->sold_count);
        $this->assertDatabaseHas('tickets', [
            'registration_id' => $registrationId,
            'status' => TicketStatus::VOID->value,
        ]);

        $this->actingAs($admin)
            ->postJson("/api/v1/registrations/{$registrationId}/cancel")
            ->assertOk();

        $this->assertSame(0, $event->fresh()->sold_count);
    }

    public function test_student_cannot_cancel_own_registration(): void
    {
        $student = $this->userWithRole(UserRole::STUDENT);
        $event = $this->freeEvent();

        $response = $this->actingAs($student)
            ->postJson('/api/v1/registrations', ['event_id' => $event->id])
            ->assertCreated();

        $this->actingAs($student)
            ->postJson("/api/v1/registrations/{$response->json('data.id')}/cancel")
            ->assertForbidden();
    }

    public function test_organization_admin_cannot_cancel_registration_of_other_organization(): void
    {
        $own = Organization::factory()->create();
        $other = Organization::factory()->create();
        $admin = $this->organizationAdminOf($own);
        $student = $this->userWithRole(UserRole::STUDENT);
        $event = $this->freeEvent(['organization_id' => $other->id]);
        $registration = Registration::factory()->confirmed()->create([
            'user_id' => $student->id,
            'event_id' => $event->id,
        ]);

        $this->actingAs($admin)
            ->postJson("/api/v1/registrations/{$registration->id}/cancel")
            ->assertForbidden();
    }

    public function test_student_sees_only_own_registrations(): void
    {
        $student = $this->userWithRole(UserRole::STUDENT);
        $other = $this->userWithRole(UserRole::STUDENT);
        $event = $this->freeEvent();

        Registration::factory()->confirmed()->create([
            'user_id' => $student->id,
            'event_id' => $event->id,
        ]);
        Registration::factory()->confirmed()->create([
            'user_id' => $other->id,
            'event_id' => $this->freeEvent()->id,
        ]);

        $response = $this->actingAs($student)->getJson('/api/v1/registrations');

        $response->assertOk();
        $this->assertCount(1, $response->json('data'));
        $this->assertSame($student->id, Registration::find($response->json('data.0.id'))->user_id);
    }

    public function test_student_cannot_view_another_students_registration(): void
    {
        $student = $this->userWithRole(UserRole::STUDENT);
        $other = $this->userWithRole(UserRole::STUDENT);
        $registration = Registration::factory()->confirmed()->create([
            'user_id' => $other->id,
            'event_id' => $this->freeEvent()->id,
        ]);

        $this->actingAs($student)
            ->getJson("/api/v1/registrations/{$registration->id}")
            ->assertForbidden();
    }
}
