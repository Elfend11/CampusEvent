<?php

namespace Tests\Feature\Integration;

use App\Enums\EventStatus;
use App\Enums\PaymentStatus;
use App\Enums\RegistrationStatus;
use App\Enums\TicketStatus;
use App\Enums\UserRole;
use App\Models\Event;
use App\Models\User;
use App\Models\UserRoleAssignment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

/**
 * Full CampusEvent journey across every module:
 * auth -> organization -> event -> registration -> reservation ->
 * payment -> e-ticket -> QR check-in -> reporting.
 */
class EndToEndFlowTest extends TestCase
{
    use RefreshDatabase;

    private const PASSWORD = 'Password123!';

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

    /**
     * @param  list<UserRole>  $roles
     */
    private function createUserThroughApi(User $superAdmin, string $name, string $email, array $roles): User
    {
        $response = $this->actingAs($superAdmin)
            ->postJson('/api/v1/users', [
                'name' => $name,
                'email' => $email,
                'password' => self::PASSWORD,
                'roles' => array_map(fn (UserRole $role) => $role->value, $roles),
            ])
            ->assertCreated();

        return User::findOrFail($response->json('data.id'));
    }

    private function login(User $user): void
    {
        Auth::guard('web')->logout();
        Auth::forgetGuards();
        $this->flushSession();

        $response = $this->postJson('/api/v1/auth/login', [
            'email' => $user->email,
            'password' => self::PASSWORD,
        ])->assertOk();

        $this->assertSame($user->id, $response->json('data.id'));

        // Feature tests do not carry the session cookie between requests, so
        // authenticate the following requests explicitly.
        $this->actingAs($user);
    }

    public function test_full_campus_event_journey_across_all_modules(): void
    {
        $superAdmin = $this->userWithRole(UserRole::SUPER_ADMIN);
        $superAdmin->update(['password' => bcrypt(self::PASSWORD)]);

        // 1. Super Admin logs in and prepares organization accounts.
        $this->login($superAdmin);

        $organization = $this->actingAs($superAdmin)
            ->postJson('/api/v1/organizations', [
                'name' => 'Badan Eksekutif Mahasiswa',
                'type' => 'BEM',
                'status' => 'ACTIVE',
            ])
            ->assertCreated()
            ->json('data');

        // 2. Admin Organisasi, Petugas Check-in and Mahasiswa accounts.
        $orgAdmin = $this->createUserThroughApi(
            $superAdmin,
            'Admin Organisasi',
            'admin.org@campusevent.test',
            [UserRole::ORGANIZATION_ADMIN, UserRole::STUDENT],
        );
        $officer = $this->createUserThroughApi(
            $superAdmin,
            'Petugas Check-in',
            'officer@campusevent.test',
            [UserRole::CHECK_IN_OFFICER],
        );
        $student = $this->createUserThroughApi(
            $superAdmin,
            'Mahasiswa Peserta',
            'student@campusevent.test',
            [UserRole::STUDENT],
        );

        $this->actingAs($superAdmin)
            ->postJson("/api/v1/organizations/{$organization['id']}/members", [
                'user_id' => $orgAdmin->id,
                'role' => 'ADMIN',
            ])
            ->assertCreated();

        // 3. Admin Organisasi logs in and creates an event.
        $this->login($orgAdmin);

        $eventPayload = [
            'organization_id' => $organization['id'],
            'title' => 'Seminar Teknologi Kampus 2026',
            'description' => 'Seminar tahunan tentang teknologi dan karier.',
            'location' => 'Auditorium Kampus',
            'category' => 'Seminar',
            'registration_start' => now()->subHour()->toDateTimeString(),
            'registration_end' => now()->addDay()->toDateTimeString(),
            'event_start' => now()->addDays(2)->toDateTimeString(),
            'event_end' => now()->addDays(2)->addHours(3)->toDateTimeString(),
            'capacity' => 2,
            'ticket_price' => 50000,
        ];

        $event = $this->actingAs($orgAdmin)
            ->postJson('/api/v1/events', $eventPayload)
            ->assertCreated()
            ->assertJsonPath('data.status', EventStatus::DRAFT->value)
            ->json('data');

        // 4. Event is published and the check-in officer is assigned.
        $this->actingAs($orgAdmin)
            ->patchJson("/api/v1/events/{$event['id']}/status", ['status' => EventStatus::PUBLISHED->value])
            ->assertOk()
            ->assertJsonPath('data.status', EventStatus::PUBLISHED->value);

        $this->actingAs($orgAdmin)
            ->postJson("/api/v1/events/{$event['id']}/officers", ['user_id' => $officer->id])
            ->assertCreated();

        // 5. Mahasiswa logs in and discovers the published event publicly.
        $this->login($student);

        Auth::forgetGuards();

        $listing = $this->getJson('/api/v1/events?search=Seminar%20Teknologi')->assertOk();
        $this->assertTrue(
            collect($listing->json('data'))->pluck('id')->contains($event['id']),
        );

        $this->getJson("/api/v1/events/{$event['id']}")
            ->assertOk()
            ->assertJsonPath('data.available_quota', 2)
            ->assertJsonPath('data.ticket_price', 50000);

        // 6. Registration reserves exactly one ticket for 10 minutes.
        $registrationResponse = $this->actingAs($student)
            ->postJson('/api/v1/registrations', ['event_id' => $event['id']])
            ->assertCreated()
            ->assertJsonPath('data.status', RegistrationStatus::PENDING->value)
            ->assertJsonPath('data.payment.status', PaymentStatus::PENDING->value)
            ->assertJsonPath('data.payment.amount', 50000);

        $registration = $registrationResponse->json('data');
        $this->assertNotNull($registration['expires_at']);

        $this->assertDatabaseHas('events', [
            'id' => $event['id'],
            'reserved_count' => 1,
            'sold_count' => 0,
        ]);

        $this->actingAs($student)
            ->getJson('/api/v1/registrations')
            ->assertOk()
            ->assertJsonPath('data.0.id', $registration['id']);

        // 7. Simulated payment confirms the registration and releases the reservation.
        $paymentResponse = $this->actingAs($student)
            ->postJson("/api/v1/payments/{$registration['payment']['id']}/simulate", [
                'success' => true,
            ])
            ->assertOk()
            ->assertJsonPath('data.status', PaymentStatus::PAID->value)
            ->assertJsonPath('data.registration.status', RegistrationStatus::CONFIRMED->value)
            ->assertJsonPath('data.registration.ticket.status', TicketStatus::ACTIVE->value);

        $ticket = $paymentResponse->json('data.registration.ticket');
        $token = $paymentResponse->json('ticket_token');
        $this->assertNotEmpty($token);

        $this->assertDatabaseHas('events', [
            'id' => $event['id'],
            'reserved_count' => 0,
            'sold_count' => 1,
        ]);
        $this->assertDatabaseCount('payments', 1);
        $this->assertDatabaseCount('tickets', 1);

        // 8. E-ticket is visible with its QR token and event details.
        $this->actingAs($student)
            ->getJson("/api/v1/tickets/{$ticket['id']}")
            ->assertOk()
            ->assertJsonPath('data.status', TicketStatus::ACTIVE->value)
            ->assertJsonPath('data.event.id', $event['id'])
            ->assertJsonPath('data.owner.id', $student->id)
            ->assertJsonPath('data.token', $token);

        $this->actingAs($student)
            ->getJson('/api/v1/tickets')
            ->assertOk()
            ->assertJsonPath('data.0.id', $ticket['id']);

        // 9. Petugas scans the QR token and attendance is recorded.
        $this->login($officer);

        $checkIn = $this->actingAs($officer)
            ->postJson('/api/v1/check-in/validate', ['token' => $token])
            ->assertOk()
            ->assertJsonPath('valid', true)
            ->assertJsonPath('data.status', TicketStatus::USED->value)
            ->assertJsonPath('data.event.id', $event['id'])
            ->assertJsonPath('data.owner.id', $student->id);

        $this->assertNotNull($checkIn->json('data.attendance.checked_in_at'));
        $this->assertDatabaseHas('attendances', [
            'ticket_id' => $ticket['id'],
            'officer_id' => $officer->id,
        ]);

        // 10. A second scan is rejected and no duplicate attendance exists.
        $this->actingAs($officer)
            ->postJson('/api/v1/check-in/validate', ['token' => $token])
            ->assertStatus(409);

        $this->assertDatabaseCount('attendances', 1);

        // 11. Admin Organisasi reads event, attendance and dashboard reports.
        $this->login($orgAdmin);

        $this->actingAs($orgAdmin)
            ->getJson("/api/v1/reports/events/{$event['id']}")
            ->assertOk()
            ->assertJsonPath('data.summary.registrations_count', 1)
            ->assertJsonPath('data.summary.confirmed_registrations_count', 1)
            ->assertJsonPath('data.summary.tickets_count', 1)
            ->assertJsonPath('data.summary.used_tickets_count', 1)
            ->assertJsonPath('data.summary.check_ins_count', 1)
            ->assertJsonPath('data.summary.paid_payments_total', 50000)
            ->assertJsonPath('data.registrations.0.user.id', $student->id);

        $this->actingAs($orgAdmin)
            ->getJson("/api/v1/reports/attendance/{$event['id']}")
            ->assertOk()
            ->assertJsonPath('data.summary.check_ins_count', 1)
            ->assertJsonPath('data.attendances.0.ticket.id', $ticket['id'])
            ->assertJsonPath('data.attendances.0.officer.id', $officer->id);

        $this->actingAs($orgAdmin)
            ->getJson('/api/v1/reports/dashboard')
            ->assertOk()
            ->assertJsonPath('data.organizations.events_count', 1)
            ->assertJsonPath('data.organizations.registrations_count', 1)
            ->assertJsonPath('data.organizations.confirmed_registrations_count', 1);

        // 12. Super Admin sees platform-wide aggregates.
        $this->actingAs($superAdmin)
            ->getJson('/api/v1/reports/dashboard')
            ->assertOk()
            ->assertJsonPath('data.platform.organizations_count', 1)
            ->assertJsonPath('data.platform.events_count', 1)
            ->assertJsonPath('data.platform.registrations_count', 1);

        // 13. Final database invariants for the whole journey.
        $freshEvent = Event::findOrFail($event['id']);
        $this->assertSame(1, $freshEvent->sold_count);
        $this->assertSame(0, $freshEvent->reserved_count);
        $this->assertSame(1, $freshEvent->availableQuota());
        $this->assertSame(EventStatus::PUBLISHED, $freshEvent->status);

        $this->assertDatabaseHas('payments', [
            'registration_id' => $registration['id'],
            'status' => PaymentStatus::PAID->value,
        ]);
        $this->assertDatabaseHas('tickets', [
            'id' => $ticket['id'],
            'status' => TicketStatus::USED->value,
        ]);
    }
}
