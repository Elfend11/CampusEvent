<?php

namespace Tests\Feature\Integration;

use App\Enums\EventStatus;
use App\Enums\RegistrationStatus;
use App\Enums\UserRole;
use App\Models\Event;
use App\Models\Registration;
use App\Models\User;
use App\Models\UserRoleAssignment;
use App\Services\PaymentService;
use App\Services\RegistrationService;
use App\Services\TicketService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery\MockInterface;
use RuntimeException;
use Tests\TestCase;

/**
 * ACID and rollback guarantees for the critical reservation flows.
 */
class TransactionIntegrityTest extends TestCase
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

    private function event(array $overrides = []): Event
    {
        return Event::factory()->create(array_merge([
            'status' => EventStatus::PUBLISHED,
            'ticket_price' => 0,
            'capacity' => 10,
            'reserved_count' => 0,
            'sold_count' => 0,
        ], $overrides));
    }

    public function test_paid_reservation_rolls_back_when_payment_record_creation_fails(): void
    {
        $student = $this->userWithRole(UserRole::STUDENT);
        $event = $this->event(['ticket_price' => 25000]);

        $this->mock(PaymentService::class, function (MockInterface $mock) {
            $mock->shouldReceive('resetToPending')
                ->once()
                ->andThrow(new RuntimeException('Simulated payment record failure'));
        });

        $this->actingAs($student)
            ->postJson('/api/v1/registrations', ['event_id' => $event->id])
            ->assertStatus(500);

        $this->assertDatabaseCount('registrations', 0);
        $this->assertDatabaseCount('payments', 0);

        $event->refresh();
        $this->assertSame(0, $event->reserved_count);
        $this->assertSame(0, $event->sold_count);
        $this->assertSame(10, $event->availableQuota());
    }

    public function test_free_registration_rolls_back_when_ticket_issuance_fails(): void
    {
        $student = $this->userWithRole(UserRole::STUDENT);
        $event = $this->event();

        $this->mock(TicketService::class, function (MockInterface $mock) {
            $mock->shouldReceive('issue')
                ->once()
                ->andThrow(new RuntimeException('Simulated ticket failure'));
        });

        $this->actingAs($student)
            ->postJson('/api/v1/registrations', ['event_id' => $event->id])
            ->assertStatus(500);

        $this->assertDatabaseCount('registrations', 0);
        $this->assertDatabaseCount('tickets', 0);

        $event->refresh();
        $this->assertSame(0, $event->sold_count);
        $this->assertSame(10, $event->availableQuota());
    }

    public function test_expiration_releases_reserved_quota_exactly_once(): void
    {
        $student = $this->userWithRole(UserRole::STUDENT);
        $event = $this->event([
            'ticket_price' => 25000,
            'reserved_count' => 1,
        ]);

        $registration = Registration::factory()->create([
            'user_id' => $student->id,
            'event_id' => $event->id,
            'expires_at' => now()->subMinute(),
        ]);

        /** @var RegistrationService $service */
        $service = $this->app->make(RegistrationService::class);

        $this->assertTrue($service->expire($registration));
        $this->assertSame(0, $event->fresh()->reserved_count);
        $this->assertSame(RegistrationStatus::EXPIRED, $registration->fresh()->status);

        $this->assertFalse($service->expire($registration->fresh()));
        $this->assertSame(0, $event->fresh()->reserved_count);

        $this->assertTrue($event->fresh()->reserved_count >= 0);
    }

    public function test_expired_reservation_does_not_leak_quota_when_released_by_payment_race(): void
    {
        $student = $this->userWithRole(UserRole::STUDENT);
        $event = $this->event([
            'ticket_price' => 25000,
            'reserved_count' => 1,
        ]);

        $registration = Registration::factory()->create([
            'user_id' => $student->id,
            'event_id' => $event->id,
            'expires_at' => now()->subMinute(),
        ]);

        $payment = $registration->payment()->create([
            'amount' => 25000,
            'status' => 'PENDING',
        ]);

        $this->actingAs($student)
            ->postJson("/api/v1/payments/{$payment->id}/simulate", ['success' => true])
            ->assertStatus(409);

        $this->artisan('registrations:expire')->assertSuccessful();

        $event->refresh();
        $this->assertSame(0, $event->reserved_count);
        $this->assertSame(0, $event->sold_count);
        $this->assertSame(RegistrationStatus::EXPIRED, $registration->fresh()->status);
        $this->assertDatabaseCount('tickets', 0);
    }
}
