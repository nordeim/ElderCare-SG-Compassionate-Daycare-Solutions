<?php

namespace Database\Factories;

use App\Models\Subscription;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Subscription>
 */
class SubscriptionFactory extends Factory
{
    protected $model = Subscription::class;

    public function definition(): array
    {
        $plans = ['free', 'monthly', 'annual'];
        $plan = fake()->randomElement($plans);
        $now = now();

        return [
            'email' => fake()->safeEmail(),
            'mailchimp_subscriber_id' => 'mc_' . Str::random(12),
            'mailchimp_status' => fake()->randomElement(['subscribed', 'pending', 'unsubscribed']),
            'preferences' => [
                'newsletter' => fake()->boolean(70),
                'promotions' => fake()->boolean(40),
            ],
            'subscribed_at' => $plan === 'free' ? $now : $now->subDays(fake()->numberBetween(0, 30)),
            'unsubscribed_at' => null,
            'last_synced_at' => now(),
        ];
    }

    public function subscribed(): static
    {
        return $this->state(fn (array $attributes) => [
            'mailchimp_status' => 'subscribed',
            'subscribed_at' => now()->subDays(fake()->numberBetween(0, 30)),
        ]);
    }

    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'mailchimp_status' => 'pending',
        ]);
    }

    public function unsubscribed(): static
    {
        return $this->state(fn (array $attributes) => [
            'mailchimp_status' => 'unsubscribed',
            'unsubscribed_at' => now()->subDays(fake()->numberBetween(0, 90)),
        ]);
    }
}
