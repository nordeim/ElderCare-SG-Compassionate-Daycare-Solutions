<?php

namespace Database\Factories;

use App\Models\Testimonial;
use App\Models\User;
use App\Models\Center;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Testimonial>
 */
class TestimonialFactory extends Factory
{
    protected $model = Testimonial::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'center_id' => Center::factory(),
            'title' => $this->generateTitle(),
            'content' => $this->generateContent(),
            'rating' => fake()->numberBetween(3, 5), // bias toward positive reviews
            'status' => 'pending',
            'moderation_notes' => null,
            'moderated_by' => null,
            'moderated_at' => null,
        ];
    }

    /**
     * Indicate an approved testimonial.
     */
    public function approved(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'approved',
                'moderated_by' => User::factory()->admin(),
                'moderated_at' => now()->subDays(fake()->numberBetween(1, 30)),
            ];
        });
    }

    /**
     * Indicate a rejected testimonial.
     */
    public function rejected(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'rejected',
                'moderated_by' => User::factory()->admin(),
                'moderated_at' => now()->subDays(fake()->numberBetween(1, 30)),
                'moderation_notes' => fake()->randomElement([
                    'Content does not meet community guidelines',
                    'Inappropriate language detected',
                    'Insufficient detail - please elaborate',
                ]),
            ];
        });
    }

    /**
     * Indicate a spam testimonial.
     */
    public function spam(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'spam',
                'moderated_by' => User::factory()->admin(),
                'moderated_at' => now()->subHours(fake()->numberBetween(1, 72)),
                'moderation_notes' => 'Marked as spam by automated/manual review',
            ];
        });
    }

    /**
     * Generate a short testimonial title.
     */
    protected function generateTitle(): string
    {
        $prefixes = ['Excellent', 'Great', 'Very Satisfied', 'Highly Recommend', 'Wonderful', 'Helpful Staff', 'Caring Team'];
        $suffixes = ['service', 'care', 'experience', 'support', 'facility', 'team'];

        return fake()->randomElement($prefixes) . ' ' . fake()->randomElement($suffixes);
    }

    /**
     * Generate realistic testimonial content.
     */
    protected function generateContent(): string
    {
        $paragraphs = fake()->paragraphs(fake()->numberBetween(1, 3));
        return implode("\n\n", $paragraphs);
    }
}
