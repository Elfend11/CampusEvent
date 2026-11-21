<?php

namespace Tests\Feature\Report;

use App\Enums\EventStatus;
use App\Enums\PaymentStatus;
use App\Enums\RegistrationStatus;
use App\Enums\TicketStatus;
use App\Enums\UserRole;
use App\Models\Attendance;
use App\Models\Event;
use App\Models\Organization;
use App\Models\OrganizationMember;
use App\Models\Payment;
use App\Models\Registration;
use App\Models\Ticket;
use App\Models\User;
use App\Models\UserRoleAssignment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportTest extends TestCase
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

    private function publishedEvent(Organization $organization, array $overrides = []): Event
    {
        return Event::factory()->create(array_merge([
            'organization_id' => $organization->id,
            'status' => EventStatus::PUBLISHED,
            'ticket_price' => 0,
            'capacity' => 10,
            'reserved_count' => 0,
            'sold_count' => 0,
        ], $overrides));
    }

    public function test_super_admin_dashboard_contains_platform_and_personal_sections(): void
    {
        $admin = $this->userWithRole(UserRole::SUPER_ADMIN);
        $organization = Organization::factory()->create();
        $event = $this->publishedEvent($organization);
        $student = $this->userWithRole(UserRole::STUDENT);

        Registration::factory()->confirmed()->create([
            'user_id' => $student->id,
            'event_id' => $event->id,
        ]);

        $response = $this->actingAs($admin)->getJson('/api/v1/reports/dashboard');

        $response
            ->assertOk()
            ->assertJsonPath('data.roles.0', UserRole::SUPER_ADMIN->value)
            ->assertJsonPath('data.platform.organizations_count', 1)
            ->assertJsonPath('data.platform.events_count', 1)
            ->assertJsonPath('data.platform.registrations_count', 1)
            ->assertJsonPath('data.organizations', null)
            ->assertJsonPath('data.officer', null)
            ->assertJsonPath('data.student.registrations_count', 0);
    }

    public function test_organization_admin_dashboard_is_scoped_to_owned_organizations(): void
    {
        $ownOrganization = Organization::factory()->create();
        $otherOrganization = Organization::factory()->create();
        $admin = $this->organizationAdminOf($ownOrganization);

        $ownEvent = $this->publishedEvent($ownOrganization);
        $this->publishedEvent($otherOrganization);

        $student = $this->userWithRole(UserRole::STUDENT);

        Registration::factory()->confirmed()->create([
            'user_id' => $student->id,
            'event_id' => $ownEvent->id,
        ]);
        Registration::factory()->confirmed()->create([
            'user_id' => $student->id,
            'event_id' => $otherOrganization->events()->first()->id,
        ]);

        $this->actingAs($admin)
            ->getJson('/api/v1/reports/dashboard')
            ->assertOk()
            ->assertJsonPath('data.platform', null)
            ->assertJsonPath('data.organizations.organizations_count', 1)
            ->assertJsonPath('data.organizations.events_count', 1)
            ->assertJsonPath('data.organizations.registrations_count', 1)
            ->assertJsonPath('data.organizations.confirmed_registrations_count', 1)
            ->assertJsonPath('data.organizations.organizations.0.id', $ownOrganization->id);
    }

    public function test_student_dashboard_returns_only_personal_statistics(): void
    {
        $organization = Organization::factory()->create();
        $event = $this->publishedEvent($organization);
        $student = $this->userWithRole(UserRole::STUDENT);

        $registration = Registration::factory()->confirmed()->create([
            'user_id' => $student->id,
            'event_id' => $event->id,
        ]);
        $ticket = Ticket::factory()->create(['registration_id' => $registration->id]);

        Payment::factory()->paid()->create([
            'registration_id' => $registration->id,
            'amount' => 25000,
        ]);

        $this->actingAs($student)
            ->getJson('/api/v1/reports/dashboard')
            ->assertOk()
            ->assertJsonPath('data.platform', null)
            ->assertJsonPath('data.organizations', null)
            ->assertJsonPath('data.officer', null)
            ->assertJsonPath('data.student.registrations_count', 1)
            ->assertJsonPath('data.student.confirmed_registrations_count', 1)
            ->assertJsonPath('data.student.tickets_count', 1)
            ->assertJsonPath('data.student.active_tickets_count', 1)
            ->assertJsonPath('data.student.paid_payments_total', 25000)
            ->assertJsonPath('data.student.recent_registrations.0.id', $registration->id)
            ->assertJsonPath('data.student.recent_registrations.0.event.id', $event->id);

        $this->assertSame($ticket->id, $ticket->fresh()->id);
    }

    public function test_officer_dashboard_lists_assigned_events_and_check_ins(): void
    {
        $organization = Organization::factory()->create();
        $officer = $this->userWithRole(UserRole::CHECK_IN_OFFICER);
        $event = $this->publishedEvent($organization);
        $otherEvent = $this->publishedEvent($organization);

        $event->officers()->attach($officer->id);

        $student = $this->userWithRole(UserRole::STUDENT);
        $registration = Registration::factory()->confirmed()->create([
            'user_id' => $student->id,
            'event_id' => $event->id,
        ]);
        $ticket = Ticket::factory()->used()->create(['registration_id' => $registration->id]);
        Attendance::factory()->create([
            'ticket_id' => $ticket->id,
            'officer_id' => $officer->id,
        ]);

        $this->actingAs($officer)
            ->getJson('/api/v1/reports/dashboard')
            ->assertOk()
            ->assertJsonPath('data.platform', null)
            ->assertJsonPath('data.organizations', null)
            ->assertJsonPath('data.officer.assigned_events_count', 1)
            ->assertJsonPath('data.officer.check_ins_count', 1)
            ->assertJsonPath('data.officer.assigned_events.0.id', $event->id);

        $this->assertNotSame($event->id, $otherEvent->id);
    }

    public function test_organization_admin_can_view_event_report_with_summary_and_filters(): void
    {
        $organization = Organization::factory()->create();
        $admin = $this->organizationAdminOf($organization);
        $event = $this->publishedEvent($organization, [
            'capacity' => 10,
            'reserved_count' => 1,
            'sold_count' => 1,
        ]);

        $confirmedStudent = User::factory()->create(['name' => 'Budi Santoso', 'email' => 'budi@example.com']);
        $pendingStudent = User::factory()->create(['name' => 'Siti Aminah', 'email' => 'siti@example.com']);

        $confirmed = Registration::factory()->confirmed()->create([
            'user_id' => $confirmedStudent->id,
            'event_id' => $event->id,
        ]);
        Payment::factory()->paid()->create([
            'registration_id' => $confirmed->id,
            'amount' => 25000,
        ]);
        $ticket = Ticket::factory()->used()->create([
            'registration_id' => $confirmed->id,
            'ticket_code' => 'TICKET-001',
        ]);
        Attendance::factory()->create(['ticket_id' => $ticket->id]);

        $pending = Registration::factory()->create([
            'user_id' => $pendingStudent->id,
            'event_id' => $event->id,
        ]);
        Payment::factory()->create([
            'registration_id' => $pending->id,
            'status' => PaymentStatus::PENDING,
        ]);

        $this->actingAs($admin)
            ->getJson("/api/v1/reports/events/{$event->id}")
            ->assertOk()
            ->assertJsonPath('data.event.id', $event->id)
            ->assertJsonPath('data.summary.capacity', 10)
            ->assertJsonPath('data.summary.registrations_count', 2)
            ->assertJsonPath('data.summary.confirmed_registrations_count', 1)
            ->assertJsonPath('data.summary.pending_registrations_count', 1)
            ->assertJsonPath('data.summary.tickets_count', 1)
            ->assertJsonPath('data.summary.used_tickets_count', 1)
            ->assertJsonPath('data.summary.check_ins_count', 1)
            ->assertJsonPath('data.summary.paid_payments_total', 25000)
            ->assertJsonPath('data.meta.total', 2)
            ->assertJsonCount(2, 'data.registrations')
            ->assertJsonPath('data.registrations.0.user.name', $pendingStudent->name);

        $this->actingAs($admin)
            ->getJson("/api/v1/reports/events/{$event->id}?status=CONFIRMED")
            ->assertOk()
            ->assertJsonPath('data.meta.total', 1)
            ->assertJsonPath('data.registrations.0.user.email', 'budi@example.com');

        $this->actingAs($admin)
            ->getJson("/api/v1/reports/events/{$event->id}?search=Siti")
            ->assertOk()
            ->assertJsonPath('data.meta.total', 1)
            ->assertJsonPath('data.registrations.0.user.id', $pendingStudent->id);

        $this->actingAs($admin)
            ->getJson("/api/v1/reports/events/{$event->id}?search=TICKET-001")
            ->assertOk()
            ->assertJsonPath('data.meta.total', 1)
            ->assertJsonPath('data.registrations.0.user.id', $confirmedStudent->id);
    }

    public function test_attendance_report_lists_check_ins_and_supports_search(): void
    {
        $organization = Organization::factory()->create();
        $admin = $this->organizationAdminOf($organization);
        $event = $this->publishedEvent($organization);
        $officer = $this->userWithRole(UserRole::CHECK_IN_OFFICER);

        $student = User::factory()->create(['name' => 'Rina Wati', 'email' => 'rina@example.com']);
        $registration = Registration::factory()->confirmed()->create([
            'user_id' => $student->id,
            'event_id' => $event->id,
        ]);
        $ticket = Ticket::factory()->used()->create([
            'registration_id' => $registration->id,
            'ticket_code' => 'CHECKIN-001',
        ]);
        $attendance = Attendance::factory()->create([
            'ticket_id' => $ticket->id,
            'officer_id' => $officer->id,
        ]);

        $this->actingAs($admin)
            ->getJson("/api/v1/reports/attendance/{$event->id}")
            ->assertOk()
            ->assertJsonPath('data.summary.check_ins_count', 1)
            ->assertJsonPath('data.meta.total', 1)
            ->assertJsonPath('data.attendances.0.id', $attendance->id)
            ->assertJsonPath('data.attendances.0.officer.id', $officer->id)
            ->assertJsonPath('data.attendances.0.ticket.ticket_code', 'CHECKIN-001')
            ->assertJsonPath('data.attendances.0.ticket.owner.email', 'rina@example.com')
            ->assertJsonPath('data.attendances.0.ticket.status', TicketStatus::USED->value);

        $this->actingAs($admin)
            ->getJson("/api/v1/reports/attendance/{$event->id}?search=Rina")
            ->assertOk()
            ->assertJsonPath('data.meta.total', 1);

        $this->actingAs($admin)
            ->getJson("/api/v1/reports/attendance/{$event->id}?search=TidakAda")
            ->assertOk()
            ->assertJsonPath('data.meta.total', 0);
    }

    public function test_organization_admin_cannot_view_reports_of_another_organization(): void
    {
        $organization = Organization::factory()->create();
        $otherOrganization = Organization::factory()->create();
        $admin = $this->organizationAdminOf($organization);
        $event = $this->publishedEvent($otherOrganization);

        $this->actingAs($admin)
            ->getJson("/api/v1/reports/events/{$event->id}")
            ->assertForbidden();

        $this->actingAs($admin)
            ->getJson("/api/v1/reports/attendance/{$event->id}")
            ->assertForbidden();
    }

    public function test_students_and_officers_cannot_view_event_reports(): void
    {
        $organization = Organization::factory()->create();
        $event = $this->publishedEvent($organization);
        $student = $this->userWithRole(UserRole::STUDENT);
        $officer = $this->userWithRole(UserRole::CHECK_IN_OFFICER);
        $event->officers()->attach($officer->id);

        $this->actingAs($student)
            ->getJson("/api/v1/reports/events/{$event->id}")
            ->assertForbidden();

        $this->actingAs($officer)
            ->getJson("/api/v1/reports/attendance/{$event->id}")
            ->assertForbidden();
    }

    public function test_super_admin_can_view_any_event_report(): void
    {
        $organization = Organization::factory()->create();
        $event = $this->publishedEvent($organization);
        $admin = $this->userWithRole(UserRole::SUPER_ADMIN);

        Registration::factory()->confirmed()->create([
            'event_id' => $event->id,
            'status' => RegistrationStatus::CONFIRMED,
        ]);

        $this->actingAs($admin)
            ->getJson("/api/v1/reports/events/{$event->id}")
            ->assertOk()
            ->assertJsonPath('data.summary.confirmed_registrations_count', 1);

        $this->actingAs($admin)
            ->getJson("/api/v1/reports/attendance/{$event->id}")
            ->assertOk()
            ->assertJsonPath('data.meta.total', 0);
    }

    public function test_guest_cannot_access_report_endpoints(): void
    {
        $organization = Organization::factory()->create();
        $event = $this->publishedEvent($organization);

        \Illuminate\Support\Facades\Auth::forgetGuards();

        $this->getJson('/api/v1/reports/dashboard')->assertUnauthorized();
        $this->getJson("/api/v1/reports/events/{$event->id}")->assertUnauthorized();
        $this->getJson("/api/v1/reports/attendance/{$event->id}")->assertUnauthorized();
    }
}
