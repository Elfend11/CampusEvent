<?php

namespace Database\Factories;

use App\Enums\RegistrationStatus;
use App\Models\Event;
use App\Models\Registration;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Registration>
 */
class RegistrationFactory extends Factory
{
    protected $model = Registration::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'event_id' => Event::factory(),
            'status' => RegistrationStatus::PENDING,
            'expires_at' => now()->addMinutes(Registration::RESERVATION_MINUTES),
        ];
    }

    public function confirmed(): static
    {
        return $this->state(fn () => [
            'status' => RegistrationStatus::CONFIRMED,
            'expires_at' => null,
        ]);
    }

    public function expired(): static
    {
        return $this->state(fn () => [
            'status' => RegistrationStatus::EXPIRED,
            'expires_at' => now()->subMinute(),
        ]);
    }
}
