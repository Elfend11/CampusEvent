<?php

namespace Tests\Feature\Integration;

use App\Enums\EventStatus;
use App\Enums\UserRole;
use App\Models\Event;
use App\Models\Organization;
use App\Models\Registration;
use App\Models\User;
use App\Models\UserRoleAssignment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Consolidated security checks: endpoint protection, RBAC and data exposure.
 */
class SecurityValidationTest extends TestCase
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

    private function publishedEvent(): Event
    {
        return Event::factory()->create([
            'status' => EventStatus::PUBLISHED,
            'ticket_price' => 0,
            'capacity' => 5,
            'reserved_count' => 0,
            'sold_count' => 0,
        ]);
    }

    public function test_guests_are_denied_every_protected_endpoint(): void
    {
        $event = $this->publishedEvent();

        Auth::forgetGuards();

        $this->getJson('/api/v1/auth/user')->assertUnauthorized();
        $this->getJson('/api/v1/profile')->assertUnauthorized();
        $this->getJson('/api/v1/registrations')->assertUnauthorized();
        $this->postJson('/api/v1/registrations', ['event_id' => $event->id])->assertUnauthorized();
        $this->getJson('/api/v1/tickets')->assertUnauthorized();
        $this->getJson('/api/v1/reports/dashboard')->assertUnauthorized();
        $this->getJson("/api/v1/reports/events/{$event->id}")->assertUnauthorized();
        $this->getJson('/api/v1/organizations')->assertUnauthorized();
        $this->getJson('/api/v1/users')->assertUnauthorized();
        $this->postJson('/api/v1/events', [])->assertUnauthorized();
        $this->postJson('/api/v1/check-in/validate', ['token' => 'x'])->assertUnauthorized();
    }

    public function test_student_cannot_access_administration_endpoints(): void
    {
        $student = $this->userWithRole(UserRole::STUDENT);
        $organization = Organization::factory()->create();
        $event = $this->publishedEvent();

        $this->actingAs($student)->getJson('/api/v1/users')->assertForbidden();
        $this->actingAs($student)->getJson('/api/v1/organizations')->assertForbidden();
        $this->actingAs($student)->getJson("/api/v1/reports/events/{$event->id}")->assertForbidden();
        $this->actingAs($student)->getJson("/api/v1/reports/attendance/{$event->id}")->assertForbidden();
        $this->actingAs($student)->postJson("/api/v1/events/{$event->id}/officers", [
            'user_id' => $student->id,
        ])->assertForbidden();
        $this->actingAs($student)->patchJson("/api/v1/events/{$event->id}/status", [
            'status' => EventStatus::CLOSED->value,
        ])->assertForbidden();

        $this->assertSame($event->id, Event::findOrFail($event->id)->id);
        $this->assertSame($organization->id, $organization->fresh()->id);
    }

    public function test_check_in_officer_cannot_access_administration_endpoints(): void
    {
        $officer = $this->userWithRole(UserRole::CHECK_IN_OFFICER);
        $event = $this->publishedEvent();

        $this->actingAs($officer)->getJson('/api/v1/users')->assertForbidden();
        $this->actingAs($officer)->getJson('/api/v1/organizations')->assertForbidden();
        $this->actingAs($officer)->getJson("/api/v1/reports/events/{$event->id}")->assertForbidden();
        $this->actingAs($officer)->postJson("/api/v1/events/{$event->id}/officers", [
            'user_id' => $officer->id,
        ])->assertForbidden();
    }

    public function test_registration_role_is_never_taken_from_frontend_payload(): void
    {
        $student = $this->userWithRole(UserRole::STUDENT);
        $organization = Organization::factory()->create();

        $this->actingAs($student)
            ->postJson('/api/v1/organizations', [
                'name' => 'Organisasi Palsu',
                'type' => 'BEM',
                'role' => UserRole::SUPER_ADMIN->value,
                'roles' => [UserRole::SUPER_ADMIN->value],
            ])
            ->assertForbidden();

        $this->actingAs($student)
            ->getJson('/api/v1/organizations')
            ->assertForbidden();

        $this->assertDatabaseMissing('organizations', ['name' => 'Organisasi Palsu']);
        $this->assertSame($organization->id, $organization->fresh()->id);
        $this->assertFalse($student->fresh()->hasRole(UserRole::SUPER_ADMIN));
        $this->assertTrue($student->fresh()->hasRole(UserRole::STUDENT));
    }

    public function test_responses_never_expose_password_or_ticket_token_hash(): void
    {
        $student = $this->userWithRole(UserRole::STUDENT);
        $event = $this->publishedEvent();

        $registration = $this->actingAs($student)
            ->postJson('/api/v1/registrations', ['event_id' => $event->id])
            ->assertCreated();

        $this->assertStringNotContainsString('password', $registration->getContent());
        $this->assertStringNotContainsString('token_hash', $registration->getContent());

        $token = $registration->json('data.ticket_token');
        $ticket = $registration->json('data.ticket');

        $this->assertNotEmpty($token);

        $stored = DB::table('tickets')->where('id', $ticket['id'])->value('token_hash');
        $this->assertNotSame($token, $stored);
        $this->assertSame(hash('sha256', $token), $stored);

        $show = $this->actingAs($student)->getJson("/api/v1/tickets/{$ticket['id']}")->assertOk();
        $this->assertStringNotContainsString('token_hash', $show->getContent());
        $this->assertStringNotContainsString('password', $show->getContent());
        $this->assertSame($token, $show->json('data.token'));
    }

    public function test_student_cannot_read_another_students_registration_or_ticket(): void
    {
        $owner = $this->userWithRole(UserRole::STUDENT);
        $other = $this->userWithRole(UserRole::STUDENT);
        $event = $this->publishedEvent();

        $registration = $this->actingAs($owner)
            ->postJson('/api/v1/registrations', ['event_id' => $event->id])
            ->assertCreated();

        $registrationId = $registration->json('data.id');
        $ticketId = $registration->json('data.ticket.id');

        $this->actingAs($other)
            ->getJson("/api/v1/registrations/{$registrationId}")
            ->assertForbidden();

        $this->actingAs($other)
            ->getJson("/api/v1/tickets/{$ticketId}")
            ->assertForbidden();

        $this->assertSame(
            $registrationId,
            Registration::findOrFail($registrationId)->id,
        );
    }
}
