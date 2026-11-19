<?php

namespace Database\Factories;

use App\Enums\EventStatus;
use App\Models\Event;
use App\Models\Organization;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Event>
 */
class EventFactory extends Factory
{
    protected $model = Event::class;

    public function definition(): array
    {
        $start = fake()->dateTimeBetween('+3 days', '+30 days');

        return [
            'organization_id' => Organization::factory(),
            'title' => fake()->sentence(4),
            'description' => fake()->paragraph(),
            'location' => fake()->randomElement(['Auditorium Kampus', 'Gedung Serbaguna', 'Lapangan Utama']),
            'category' => fake()->randomElement(['Seminar', 'Workshop', 'Kompetisi', 'Konser']),
            'registration_start' => now()->subDay(),
            'registration_end' => now()->addDays(2),
            'event_start' => $start,
            'event_end' => (clone $start)->modify('+4 hours'),
            'capacity' => 100,
            'reserved_count' => 0,
            'sold_count' => 0,
            'ticket_price' => 0,
            'status' => EventStatus::PUBLISHED,
        ];
    }

    public function draft(): static
    {
        return $this->state(fn () => ['status' => EventStatus::DRAFT]);
    }

    public function paid(float $price = 25000): static
    {
        return $this->state(fn () => ['ticket_price' => $price]);
    }
}
