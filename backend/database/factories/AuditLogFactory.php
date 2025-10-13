<?php

namespace Database\Factories;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\AuditLog>
 */
class AuditLogFactory extends Factory
{
    protected $model = AuditLog::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $action = fake()->randomElement(['created', 'updated', 'deleted', 'restored']);

        return [
            'user_id' => User::factory(),
            'auditable_type' => 'App\\Models\\User',
            'auditable_id' => fake()->numberBetween(1, 100),
            'action' => $action,
            'old_values' => $action === 'created' ? null : ['name' => fake()->name()],
            'new_values' => $action === 'deleted' ? null : ['name' => fake()->name()],
            'ip_address' => fake()->ipv4(),
            'user_agent' => fake()->userAgent(),
            'url' => fake()->url(),
        ];
    }

    /**
     * Indicate a creation audit log.
     */
    public function created(): static
    {
        return $this->state(fn (array $attributes) => [
            'action' => 'created',
            'old_values' => null,
            'new_values' => ['name' => fake()->name(), 'email' => fake()->email()],
        ]);
    }

    /**
     * Indicate an update audit log.
     */
    public function updated(): static
    {
        return $this->state(fn (array $attributes) => [
            'action' => 'updated',
            'old_values' => ['name' => fake()->name()],
            'new_values' => ['name' => fake()->name()],
        ]);
    }

    /**
     * Indicate a deletion audit log.
     */
    public function deleted(): static
    {
        return $this->state(fn (array $attributes) => [
            'action' => 'deleted',
            'old_values' => ['name' => fake()->name(), 'email' => fake()->email()],
            'new_values' => null,
        ]);
    }
}
