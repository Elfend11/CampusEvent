<?php

namespace Tests\Feature\Database;

use App\Enums\EventStatus;
use App\Enums\RegistrationStatus;
use App\Enums\TicketStatus;
use App\Models\Event;
use App\Models\Organization;
use App\Models\Registration;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class SchemaTest extends TestCase
{
    use RefreshDatabase;

    public function test_expected_tables_exist(): void
    {
        foreach ([
            'users',
            'user_roles',
            'organizations',
            'organization_members',
            'events',
            'event_officers',
            'registrations',
            'payments',
            'tickets',
            'attendances',
        ] as $table) {
            $this->assertTrue(Schema::hasTable($table), "Table [{$table}] is missing.");
        }
    }

    public function test_tables_use_innodb(): void
    {
        $tables = ['users', 'events', 'registrations', 'payments', 'tickets', 'attendances'];

        foreach ($tables as $table) {
            $engine = DB::selectOne(
                'SELECT ENGINE FROM information_schema.TABLES WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ?',
                [config('database.connections.mysql.database'), $table],
            );

            $this->assertSame('InnoDB', $engine->ENGINE ?? null, "Table [{$table}] is not InnoDB.");
        }
    }

    public function test_event_status_is_cast_to_enum(): void
    {
        $event = Event::factory()->create(['status' => EventStatus::DRAFT]);

        $this->assertInstanceOf(EventStatus::class, $event->fresh()->status);
        $this->assertSame(EventStatus::DRAFT, $event->fresh()->status);
    }

    public function test_duplicate_registration_is_rejected(): void
    {
        $user = User::factory()->create();
        $event = Event::factory()->create();

        Registration::factory()->create(['user_id' => $user->id, 'event_id' => $event->id]);

        $this->expectException(QueryException::class);

        Registration::factory()->create(['user_id' => $user->id, 'event_id' => $event->id]);
    }

    public function test_duplicate_ticket_code_is_rejected(): void
    {
        $first = Ticket::factory()->create(['ticket_code' => 'fixed-code']);

        $this->expectException(QueryException::class);

        Ticket::factory()->create(['ticket_code' => 'fixed-code']);
    }

    public function test_status_helpers_reflect_stored_values(): void
    {
        $registration = Registration::factory()->create(['status' => RegistrationStatus::PENDING]);

        $this->assertTrue($registration->isPending());
        $this->assertTrue($registration->hasActiveReservation());

        $ticket = Ticket::factory()->create(['status' => TicketStatus::ACTIVE]);
        $this->assertTrue($ticket->isActive());

        $organization = Organization::factory()->create();
        $this->assertSame('ACTIVE', $organization->status);
    }
}
