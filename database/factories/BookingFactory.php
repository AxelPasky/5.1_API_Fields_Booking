<?php

namespace Database\Factories;

use App\Models\Field;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Booking>
 */
class BookingFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $start_time = $this->faker->dateTimeBetween('+1 day', '+2 days');
        $end_time = (clone $start_time)->modify('+1 hour');

        return [
            'field_id' => Field::factory(),
            'user_id' => User::factory(),
            'start_time' => $start_time,
            'end_time' => $end_time,
            'total_price' => $this->faker->randomFloat(2, 20, 100),
            'status' => 'confirmed',
        ];
    }
}
