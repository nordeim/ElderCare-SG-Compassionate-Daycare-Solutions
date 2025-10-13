<?php

namespace Database\Factories;

use App\Models\Profile;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Profile>
 */
class ProfileFactory extends Factory
{
    protected $model = Profile::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'avatar' => fake()->boolean(30) ? fake()->imageUrl(200, 200, 'people') : null,
            'bio' => fake()->boolean(70) ? fake()->paragraph(3) : null,
            'birth_date' => fake()->boolean(80) ? fake()->dateTimeBetween('-90 years', '-60 years')->format('Y-m-d') : null,
            'address' => fake()->boolean(60) ? fake()->streetAddress() : null,
            'city' => fake()->boolean(60) ? fake()->randomElement([
                'Singapore',
                'Ang Mo Kio',
                'Bedok',
                'Jurong East',
                'Tampines',
                'Woodlands',
                'Yishun',
            ]) : null,
            'postal_code' => fake()->boolean(60) ? fake()->numerify('######') : null,
            'country' => 'Singapore',
        ];
    }

    /**
     * Indicate a complete profile.
     */
    public function complete(): static
    {
        return $this->state(fn (array $attributes) => [
            'avatar' => fake()->imageUrl(200, 200, 'people'),
            'bio' => fake()->paragraph(3),
            'birth_date' => fake()->dateTimeBetween('-85 years', '-65 years')->format('Y-m-d'),
            'address' => fake()->streetAddress(),
            'city' => fake()->randomElement(['Singapore', 'Ang Mo Kio', 'Bedok', 'Jurong East']),
            'postal_code' => fake()->numerify('######'),
        ]);
    }

    /**
     * Indicate a minimal profile.
     */
    public function minimal(): static
    {
        return $this->state(fn (array $attributes) => [
            'avatar' => null,
            'bio' => null,
            'birth_date' => null,
            'address' => null,
            'city' => null,
            'postal_code' => null,
        ]);
    }
}
