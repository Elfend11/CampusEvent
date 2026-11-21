<?php

namespace Tests\Feature\CheckIn;

use App\Enums\EventStatus;
use App\Enums\TicketStatus;
use App\Enums\UserRole;
use App\Models\Attendance;
use App\Models\Event;
use App\Models\Ticket;
use App\Models\User;
use App\Models\UserRoleAssignment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CheckInTest extends TestCase
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

    public function test_assigned_officer_can_check_in_valid_ticket(): void
    {
        $student = $this->userWithRole(UserRole::STUDENT);
        $officer = $this->userWithRole(UserRole::CHECK_IN_OFFICER);
        [$event, $registration, $token] = $this->issueFreeTicket($student);
        $event->officers()->attach($officer->id);

        $response = $this->actingAs($officer)
            ->postJson('/api/v1/check-in/validate', ['token' => $token]);

        $response
            ->assertOk()
            ->assertJsonPath('valid', true)
            ->assertJsonPath('data.status', TicketStatus::USED->value)
            ->assertJsonPath('data.event.id', $event->id)
            ->assertJsonPath('data.owner.id', $student->id);

        $this->assertNotNull($response->json('data.attendance.checked_in_at'));

        $this->assertDatabaseHas('attendances', [
            'ticket_id' => $registration['ticket']['id'],
            'officer_id' => $officer->id,
        ]);
        $this->assertSame(TicketStatus::USED, Ticket::find($registration['ticket']['id'])->status);
        $this->assertSame(1, Attendance::count());
    }

    public function test_check_in_rejects_unknown_token(): void
    {
        $officer = $this->userWithRole(UserRole::CHECK_IN_OFFICER);

        $this->actingAs($officer)
            ->postJson('/api/v1/check-in/validate', ['token' => 'token-palsu'])
            ->assertNotFound();
    }

    public function test_officer_not_assigned_to_event_cannot_check_in_ticket(): void
    {
        $student = $this->userWithRole(UserRole::STUDENT);
        $officer = $this->userWithRole(UserRole::CHECK_IN_OFFICER);
        [, , $token] = $this->issueFreeTicket($student);

        $this->actingAs($officer)
            ->postJson('/api/v1/check-in/validate', ['token' => $token])
            ->assertForbidden();

        $this->assertSame(0, Attendance::count());
    }

    public function test_check_in_rejects_ticket_for_different_event(): void
    {
        $student = $this->userWithRole(UserRole::STUDENT);
        $officer = $this->userWithRole(UserRole::CHECK_IN_OFFICER);
        [$event, , $token] = $this->issueFreeTicket($student);
        $event->officers()->attach($officer->id);
        $otherEvent = $this->freeEvent();

        $this->actingAs($officer)
            ->postJson('/api/v1/check-in/validate', [
                'token' => $token,
                'event_id' => $otherEvent->id,
            ])
            ->assertStatus(422);

        $this->assertSame(0, Attendance::count());
    }

    public function test_second_check_in_is_rejected_and_attendance_recorded_once(): void
    {
        $student = $this->userWithRole(UserRole::STUDENT);
        $officer = $this->userWithRole(UserRole::CHECK_IN_OFFICER);
        [$event, $registration, $token] = $this->issueFreeTicket($student);
        $event->officers()->attach($officer->id);

        $this->actingAs($officer)
            ->postJson('/api/v1/check-in/validate', ['token' => $token])
            ->assertOk();

        $this->actingAs($officer)
            ->postJson('/api/v1/check-in/validate', ['token' => $token])
            ->assertStatus(409)
            ->assertJsonPath('data.status', TicketStatus::USED->value);

        $this->assertSame(1, Attendance::count());
        $this->assertSame(
            1,
            Attendance::where('ticket_id', $registration['ticket']['id'])->count(),
        );
    }

    public function test_void_ticket_cannot_be_checked_in(): void
    {
        $student = $this->userWithRole(UserRole::STUDENT);
        $officer = $this->userWithRole(UserRole::CHECK_IN_OFFICER);
        [$event, $registration, $token] = $this->issueFreeTicket($student);
        $event->officers()->attach($officer->id);

        Ticket::find($registration['ticket']['id'])->update(['status' => TicketStatus::VOID]);

        $this->actingAs($officer)
            ->postJson('/api/v1/check-in/validate', ['token' => $token])
            ->assertStatus(409);

        $this->assertSame(0, Attendance::count());
    }

    public function test_student_cannot_use_check_in_endpoint(): void
    {
        $student = $this->userWithRole(UserRole::STUDENT);
        [, , $token] = $this->issueFreeTicket($student);

        $this->actingAs($student)
            ->postJson('/api/v1/check-in/validate', ['token' => $token])
            ->assertForbidden();
    }

    public function test_guest_cannot_use_check_in_endpoint(): void
    {
        $student = $this->userWithRole(UserRole::STUDENT);
        [, , $token] = $this->issueFreeTicket($student);

        \Illuminate\Support\Facades\Auth::forgetGuards();

        $this->postJson('/api/v1/check-in/validate', ['token' => $token])
            ->assertUnauthorized();
    }

    public function test_super_admin_can_check_in_without_officer_assignment(): void
    {
        $student = $this->userWithRole(UserRole::STUDENT);
        $admin = $this->userWithRole(UserRole::SUPER_ADMIN);
        [, , $token] = $this->issueFreeTicket($student);

        $this->actingAs($admin)
            ->postJson('/api/v1/check-in/validate', ['token' => $token])
            ->assertOk();

        $this->assertSame(1, Attendance::count());
    }
}
