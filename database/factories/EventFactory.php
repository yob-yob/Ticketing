<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Event>
 */
class EventFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => $this->faker->title(),
            'description' => $this->faker->sentence,
            'datetime' => now()->addDay(),
            'location' => $this->faker->city(),
            'price' => $this->faker->numberBetween(100000, 1000000), // 1,000 to 10,000
        ];
    }
}
