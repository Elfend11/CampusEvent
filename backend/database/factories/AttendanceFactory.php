<?php

namespace Database\Factories;

use App\Models\Attendance;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Attendance>
 */
class AttendanceFactory extends Factory
{
    protected $model = Attendance::class;

    public function definition(): array
    {
        return [
            'ticket_id' => Ticket::factory(),
            'officer_id' => User::factory(),
            'checked_in_at' => now(),
        ];
    }
}
