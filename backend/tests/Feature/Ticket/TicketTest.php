<?php

namespace Tests\Feature\Ticket;

use App\Enums\EventStatus;
use App\Enums\TicketStatus;
use App\Enums\UserRole;
use App\Models\Event;
use App\Models\Ticket;
use App\Models\User;
use App\Models\UserRoleAssignment;
use App\Services\TicketService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TicketTest extends TestCase
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

    /**
     * @return array{0: Event, 1: array<string, mixed>, 2: string}
     */
    private function issueFreeTicket(User $student, ?Event $event = null): array
    {
        $event ??= $this->freeEvent();

        $response = $this->actingAs($student)
            ->postJson('/api/v1/registrations', ['event_id' => $event->id])
            ->assertCreated();

        return [$event, $response->json('data'), $response->json('data.ticket_token')];
    }

    public function test_ticket_show_returns_qr_token_for_owner_and_stores_only_hash(): void
    {
        $student = $this->userWithRole(UserRole::STUDENT);
        [$event, $registration, $token] = $this->issueFreeTicket($student);

        $response = $this->actingAs($student)
            ->getJson("/api/v1/tickets/{$registration['ticket']['id']}");

        $response
            ->assertOk()
            ->assertJsonPath('data.id', $registration['ticket']['id'])
            ->assertJsonPath('data.ticket_code', $registration['ticket']['ticket_code'])
            ->assertJsonPath('data.status', TicketStatus::ACTIVE->value)
            ->assertJsonPath('data.event.id', $event->id)
            ->assertJsonPath('data.owner.id', $student->id)
            ->assertJsonPath('data.token', $token);

        $ticket = Ticket::find($registration['ticket']['id']);
        $this->assertSame(hash('sha256', $token), $ticket->token_hash);
        $this->assertNotSame($token, $ticket->token_hash);
    }

    public function test_ticket_token_is_unique_per_ticket(): void
    {
        $first = $this->userWithRole(UserRole::STUDENT);
        $second = $this->userWithRole(UserRole::STUDENT);

        [, , $firstToken] = $this->issueFreeTicket($first);
        [, , $secondToken] = $this->issueFreeTicket($second);

        $this->assertNotSame($firstToken, $secondToken);
        $this->assertDatabaseCount('tickets', 2);
    }

    public function test_student_sees_only_own_tickets(): void
    {
        $student = $this->userWithRole(UserRole::STUDENT);
        $other = $this->userWithRole(UserRole::STUDENT);

        [$event, $registration] = $this->issueFreeTicket($student);
        $this->issueFreeTicket($other);

        $response = $this->actingAs($student)->getJson('/api/v1/tickets');

        $response->assertOk();
        $this->assertCount(1, $response->json('data'));
        $this->assertSame($registration['ticket']['id'], $response->json('data.0.id'));
        $this->assertSame($event->id, $response->json('data.0.event.id'));
    }

    public function test_student_cannot_view_another_students_ticket(): void
    {
        $student = $this->userWithRole(UserRole::STUDENT);
        $other = $this->userWithRole(UserRole::STUDENT);
        [, $registration] = $this->issueFreeTicket($other);

        $this->actingAs($student)
            ->getJson("/api/v1/tickets/{$registration['ticket']['id']}")
            ->assertForbidden();
    }

    public function test_assigned_officer_can_validate_active_ticket(): void
    {
        $student = $this->userWithRole(UserRole::STUDENT);
        $officer = $this->userWithRole(UserRole::CHECK_IN_OFFICER);
        [$event, , $token] = $this->issueFreeTicket($student);
        $event->officers()->attach($officer->id);

        $this->actingAs($officer)
            ->postJson('/api/v1/tickets/validate', [
                'token' => $token,
                'event_id' => $event->id,
            ])
            ->assertOk()
            ->assertJsonPath('valid', true)
            ->assertJsonPath('data.event.id', $event->id)
            ->assertJsonPath('data.status', TicketStatus::ACTIVE->value);
    }

    public function test_validation_rejects_unknown_token(): void
    {
        $officer = $this->userWithRole(UserRole::CHECK_IN_OFFICER);

        $this->actingAs($officer)
            ->postJson('/api/v1/tickets/validate', ['token' => 'tidak-ada'])
            ->assertNotFound();
    }

    public function test_validation_rejects_ticket_from_another_event(): void
    {
        $student = $this->userWithRole(UserRole::STUDENT);
        $officer = $this->userWithRole(UserRole::CHECK_IN_OFFICER);
        [, , $token] = $this->issueFreeTicket($student);
        $otherEvent = $this->freeEvent();

        $this->actingAs($officer)
            ->postJson('/api/v1/tickets/validate', [
                'token' => $token,
                'event_id' => $otherEvent->id,
            ])
            ->assertStatus(422)
            ->assertJsonPath('message', 'Tiket bukan untuk event ini.');
    }

    public function test_validation_rejects_used_or_void_ticket(): void
    {
        $student = $this->userWithRole(UserRole::STUDENT);
        $officer = $this->userWithRole(UserRole::CHECK_IN_OFFICER);
        [, $registration, $token] = $this->issueFreeTicket($student);

        Ticket::find($registration['ticket']['id'])->update(['status' => TicketStatus::USED]);

        $this->actingAs($officer)
            ->postJson('/api/v1/tickets/validate', ['token' => $token])
            ->assertStatus(409);

        Ticket::find($registration['ticket']['id'])->update(['status' => TicketStatus::VOID]);

        $this->actingAs($officer)
            ->postJson('/api/v1/tickets/validate', ['token' => $token])
            ->assertStatus(409);
    }

    public function test_student_cannot_use_validation_endpoint(): void
    {
        $student = $this->userWithRole(UserRole::STUDENT);
        [, , $token] = $this->issueFreeTicket($student);

        $this->actingAs($student)
            ->postJson('/api/v1/tickets/validate', ['token' => $token])
            ->assertForbidden();
    }

    public function test_guest_cannot_access_ticket_endpoints(): void
    {
        $student = $this->userWithRole(UserRole::STUDENT);
        [, $registration, $token] = $this->issueFreeTicket($student);

        \Illuminate\Support\Facades\Auth::forgetGuards();

        $this->getJson('/api/v1/tickets')->assertUnauthorized();
        $this->getJson("/api/v1/tickets/{$registration['ticket']['id']}")->assertUnauthorized();
        $this->postJson('/api/v1/tickets/validate', ['token' => $token])->assertUnauthorized();
    }

    public function test_ticket_service_does_not_issue_duplicate_ticket_for_same_registration(): void
    {
        $student = $this->userWithRole(UserRole::STUDENT);
        [, $registration] = $this->issueFreeTicket($student);

        $ticket = Ticket::find($registration['ticket']['id']);
        $service = app(TicketService::class);

        [$again, $token] = $service->issue($ticket->registration);

        $this->assertSame($ticket->id, $again->id);
        $this->assertNull($token);
        $this->assertDatabaseCount('tickets', 1);
    }
}
