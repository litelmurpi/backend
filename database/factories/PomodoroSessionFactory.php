<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\PomodoroSession>
 */
class PomodoroSessionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $sessionTypes = ['work', 'short_break', 'long_break'];
        $statuses = ['active', 'completed', 'cancelled', 'paused'];
        
        return [
            'user_id' => \App\Models\User::factory(),
            'session_type' => $this->faker->randomElement($sessionTypes),
            'planned_duration' => $this->faker->numberBetween(5, 60),
            'actual_duration' => $this->faker->optional()->numberBetween(5, 65),
            'status' => $this->faker->randomElement($statuses),
            'started_at' => $this->faker->dateTimeBetween('-1 week', 'now'),
            'completed_at' => $this->faker->optional()->dateTimeBetween('now', '+1 hour'),
            'notes' => $this->faker->optional()->sentence(),
        ];
    }
}
