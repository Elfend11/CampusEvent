<?php

namespace Tests\Feature\Payment;

use App\Enums\EventStatus;
use App\Enums\PaymentStatus;
use App\Enums\RegistrationStatus;
use App\Enums\TicketStatus;
use App\Enums\UserRole;
use App\Models\Event;
use App\Models\Payment;
use App\Models\Registration;
use App\Models\Ticket;
use App\Models\User;
use App\Models\UserRoleAssignment;
use App\Services\TicketService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery\MockInterface;
use RuntimeException;
use Tests\TestCase;

class PaymentTest extends TestCase
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

    private function paidEvent(array $overrides = []): Event
    {
        return Event::factory()->create(array_merge([
            'status' => EventStatus::PUBLISHED,
            'ticket_price' => 25000,
            'capacity' => 10,
            'reserved_count' => 0,
            'sold_count' => 0,
        ], $overrides));
    }

    /**
     * @return array{0: User, 1: Event, 2: array<string, mixed>}
     */
    private function reserve(User $student, ?Event $event = null): array
    {
        $event ??= $this->paidEvent();

        $response = $this->actingAs($student)
            ->postJson('/api/v1/registrations', ['event_id' => $event->id])
            ->assertCreated();

        return [$student, $event, $response->json('data')];
    }

    public function test_paid_registration_creates_pending_payment_record(): void
    {
        $student = $this->userWithRole(UserRole::STUDENT);
        $event = $this->paidEvent();

        $response = $this->actingAs($student)
            ->postJson('/api/v1/registrations', ['event_id' => $event->id]);

        $response
            ->assertCreated()
            ->assertJsonPath('data.status', RegistrationStatus::PENDING->value)
            ->assertJsonPath('data.payment.status', PaymentStatus::PENDING->value)
            ->assertJsonPath('data.payment.amount', 25000);

        $this->assertDatabaseHas('payments', [
            'registration_id' => $response->json('data.id'),
            'status' => PaymentStatus::PENDING->value,
        ]);
    }

    public function test_successful_simulation_confirms_registration_and_issues_ticket(): void
    {
        $student = $this->userWithRole(UserRole::STUDENT);
        [, $event, $registration] = $this->reserve($student);

        $this->assertSame(1, $event->fresh()->reserved_count);

        $response = $this->actingAs($student)
            ->postJson("/api/v1/payments/{$registration['payment']['id']}/simulate", [
                'success' => true,
            ]);

        $response
            ->assertOk()
            ->assertJsonPath('data.status', PaymentStatus::PAID->value)
            ->assertJsonPath('data.registration.status', RegistrationStatus::CONFIRMED->value)
            ->assertJsonPath('data.registration.ticket.status', TicketStatus::ACTIVE->value);

        $this->assertNotEmpty($response->json('ticket_token'));
        $this->assertNotNull($response->json('data.paid_at'));

        $event->refresh();
        $this->assertSame(0, $event->reserved_count);
        $this->assertSame(1, $event->sold_count);
        $this->assertSame(9, $event->availableQuota());

        $this->assertDatabaseCount('payments', 1);
        $this->assertDatabaseCount('tickets', 1);
        $this->assertDatabaseHas('tickets', [
            'registration_id' => $registration['id'],
            'status' => TicketStatus::ACTIVE->value,
        ]);
    }

    public function test_failed_simulation_keeps_reservation_and_allows_retry(): void
    {
        $student = $this->userWithRole(UserRole::STUDENT);
        [, $event, $registration] = $this->reserve($student);
        $paymentId = $registration['payment']['id'];

        $this->actingAs($student)
            ->postJson("/api/v1/payments/{$paymentId}/simulate", ['success' => false])
            ->assertOk()
            ->assertJsonPath('data.status', PaymentStatus::FAILED->value)
            ->assertJsonPath('data.registration.status', RegistrationStatus::PENDING->value)
            ->assertJsonPath('data.registration.ticket', null);

        $this->assertSame(1, $event->fresh()->reserved_count);
        $this->assertSame(0, $event->fresh()->sold_count);
        $this->assertDatabaseCount('tickets', 0);

        $retry = $this->actingAs($student)
            ->postJson("/api/v1/payments/{$paymentId}/simulate", ['success' => true]);

        $retry
            ->assertOk()
            ->assertJsonPath('data.id', $paymentId)
            ->assertJsonPath('data.status', PaymentStatus::PAID->value)
            ->assertJsonPath('data.registration.status', RegistrationStatus::CONFIRMED->value);

        $this->assertSame(1, $event->fresh()->sold_count);
        $this->assertDatabaseCount('payments', 1);
        $this->assertDatabaseCount('tickets', 1);
    }

    public function test_payment_after_reservation_expiry_is_rejected_and_quota_released_once(): void
    {
        $student = $this->userWithRole(UserRole::STUDENT);
        [, $event, $registration] = $this->reserve($student);
        $paymentId = $registration['payment']['id'];

        $this->travel(11)->minutes();

        $this->actingAs($student)
            ->postJson("/api/v1/payments/{$paymentId}/simulate", ['success' => true])
            ->assertStatus(409)
            ->assertJsonPath('data.status', PaymentStatus::FAILED->value)
            ->assertJsonPath('data.registration.status', RegistrationStatus::EXPIRED->value);

        $event->refresh();
        $this->assertSame(0, $event->reserved_count);
        $this->assertSame(0, $event->sold_count);
        $this->assertDatabaseCount('tickets', 0);

        $this->artisan('registrations:expire')->assertSuccessful();
        $this->assertSame(0, $event->fresh()->reserved_count);

        $this->actingAs($student)
            ->postJson("/api/v1/payments/{$paymentId}/simulate", ['success' => true])
            ->assertStatus(409);

        $this->assertSame(0, $event->fresh()->reserved_count);
        $this->assertDatabaseCount('tickets', 0);
    }

    public function test_repeated_simulation_after_paid_does_not_issue_duplicate_ticket(): void
    {
        $student = $this->userWithRole(UserRole::STUDENT);
        [, $event, $registration] = $this->reserve($student);
        $paymentId = $registration['payment']['id'];

        $first = $this->actingAs($student)
            ->postJson("/api/v1/payments/{$paymentId}/simulate", ['success' => true])
            ->assertOk();

        $ticketId = $first->json('data.registration.ticket.id');
        $ticketCode = $first->json('data.registration.ticket.ticket_code');

        $second = $this->actingAs($student)
            ->postJson("/api/v1/payments/{$paymentId}/simulate", ['success' => true])
            ->assertOk()
            ->assertJsonPath('data.id', $paymentId)
            ->assertJsonPath('data.status', PaymentStatus::PAID->value)
            ->assertJsonPath('data.registration.ticket.id', $ticketId)
            ->assertJsonPath('data.registration.ticket.ticket_code', $ticketCode)
            ->assertJsonPath('ticket_token', null);

        $this->assertSame($ticketId, $second->json('data.registration.ticket.id'));
        $this->assertSame(1, $event->fresh()->sold_count);
        $this->assertSame(0, $event->fresh()->reserved_count);
        $this->assertSame(1, Ticket::count());
    }

    public function test_student_can_view_own_payment_status(): void
    {
        $student = $this->userWithRole(UserRole::STUDENT);
        [, , $registration] = $this->reserve($student);

        $this->actingAs($student)
            ->getJson("/api/v1/payments/{$registration['payment']['id']}")
            ->assertOk()
            ->assertJsonPath('data.status', PaymentStatus::PENDING->value)
            ->assertJsonPath('data.registration.id', $registration['id'])
            ->assertJsonPath('data.registration.event.id', $registration['event']['id']);
    }

    public function test_student_cannot_view_or_simulate_another_students_payment(): void
    {
        $owner = $this->userWithRole(UserRole::STUDENT);
        $other = $this->userWithRole(UserRole::STUDENT);
        [, , $registration] = $this->reserve($owner);
        $paymentId = $registration['payment']['id'];

        $this->actingAs($other)
            ->getJson("/api/v1/payments/{$paymentId}")
            ->assertForbidden();

        $this->actingAs($other)
            ->postJson("/api/v1/payments/{$paymentId}/simulate", ['success' => true])
            ->assertForbidden();

        $this->assertDatabaseHas('payments', [
            'id' => $paymentId,
            'status' => PaymentStatus::PENDING->value,
        ]);
        $this->assertSame(1, Event::find($registration['event']['id'])->reserved_count);
    }

    public function test_super_admin_can_view_payment_but_cannot_simulate_it(): void
    {
        $student = $this->userWithRole(UserRole::STUDENT);
        $admin = $this->userWithRole(UserRole::SUPER_ADMIN);
        [, , $registration] = $this->reserve($student);
        $paymentId = $registration['payment']['id'];

        $this->actingAs($admin)
            ->getJson("/api/v1/payments/{$paymentId}")
            ->assertOk();

        $this->actingAs($admin)
            ->postJson("/api/v1/payments/{$paymentId}/simulate", ['success' => true])
            ->assertForbidden();
    }

    public function test_guest_cannot_access_payment_endpoints(): void
    {
        $student = $this->userWithRole(UserRole::STUDENT);
        [, , $registration] = $this->reserve($student);
        $paymentId = $registration['payment']['id'];

        \Illuminate\Support\Facades\Auth::forgetGuards();

        $this->getJson("/api/v1/payments/{$paymentId}")->assertUnauthorized();
        $this->postJson("/api/v1/payments/{$paymentId}/simulate")->assertUnauthorized();
    }

    public function test_transaction_rolls_back_when_ticket_issuance_fails(): void
    {
        $student = $this->userWithRole(UserRole::STUDENT);
        [, $event, $registration] = $this->reserve($student);
        $paymentId = $registration['payment']['id'];

        $this->mock(TicketService::class, function (MockInterface $mock) {
            $mock->shouldReceive('issue')
                ->once()
                ->andThrow(new RuntimeException('Simulated ticket failure'));
        });

        $this->actingAs($student)
            ->postJson("/api/v1/payments/{$paymentId}/simulate", ['success' => true])
            ->assertStatus(500);

        $this->assertSame(PaymentStatus::PENDING, Payment::find($paymentId)->status);
        $this->assertSame(
            RegistrationStatus::PENDING,
            Registration::find($registration['id'])->status,
        );

        $event->refresh();
        $this->assertSame(1, $event->reserved_count);
        $this->assertSame(0, $event->sold_count);
        $this->assertDatabaseCount('tickets', 0);
    }

    public function test_cancelled_registration_cannot_be_paid(): void
    {
        $student = $this->userWithRole(UserRole::STUDENT);
        [, , $registration] = $this->reserve($student);
        $paymentId = $registration['payment']['id'];

        Registration::find($registration['id'])->update([
            'status' => RegistrationStatus::CANCELLED,
        ]);

        $this->actingAs($student)
            ->postJson("/api/v1/payments/{$paymentId}/simulate", ['success' => true])
            ->assertStatus(409)
            ->assertJsonPath('data.status', PaymentStatus::PENDING->value);

        $this->assertDatabaseCount('tickets', 0);
    }
}
