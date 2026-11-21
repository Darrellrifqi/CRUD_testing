<?php

namespace Database\Factories;

use App\Models\Courier;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Courier>
 */
class CourierFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<\App\Models\Courier>
     */
    protected $model = Courier::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name'             => fake()->name(),
            'phone'            => '08' . fake()->numerify('#########'),
            'email'            => fake()->unique()->safeEmail(),
            'nik'              => fake()->unique()->numerify('################'),
            'address'          => fake()->streetAddress(),
            'city'             => fake()->city(),
            'vehicle_type'     => fake()->randomElement(Courier::VEHICLE_TYPES),
            'vehicle_plate'    => strtoupper(fake()->bothify('?? #### ???')),
            'level'            => fake()->numberBetween(1, 5),
            'is_active'        => fake()->boolean(80),
            'rating'           => fake()->randomFloat(2, 3.0, 5.0),
            'total_deliveries' => fake()->numberBetween(0, 2000),
            'registered_at'    => fake()->dateTimeBetween('-3 years', 'now')->format('Y-m-d'),
        ];
    }

    /**
     * State: active courier.
     */
    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => true,
        ]);
    }

    /**
     * State: set a specific level.
     */
    public function level(int $level): static
    {
        return $this->state(fn (array $attributes) => [
            'level' => $level,
        ]);
    }
}
