<?php

namespace Database\Factories;

use App\Enums\TicketStatus;
use App\Models\Registration;
use App\Models\Ticket;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Ticket>
 */
class TicketFactory extends Factory
{
    protected $model = Ticket::class;

    public function definition(): array
    {
        return [
            'registration_id' => Registration::factory(),
            'ticket_code' => (string) Str::uuid(),
            'token_hash' => hash('sha256', Str::random(64)),
            'status' => TicketStatus::ACTIVE,
        ];
    }

    public function used(): static
    {
        return $this->state(fn () => ['status' => TicketStatus::USED]);
    }

    public function void(): static
    {
        return $this->state(fn () => ['status' => TicketStatus::VOID]);
    }
}
