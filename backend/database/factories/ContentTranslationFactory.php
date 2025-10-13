<?php

namespace Database\Factories;

use App\Models\ContentTranslation;
use App\Models\Center;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ContentTranslation>
 */
class ContentTranslationFactory extends Factory
{
    protected $model = ContentTranslation::class;

    public function definition(): array
    {
        $fields = ['short_description', 'description', 'meta_title', 'meta_description', 'question', 'answer'];

        return [
            'translatable_type' => Center::class,
            'translatable_id' => Center::factory(),
            'locale' => fake()->randomElement(['en', 'zh', 'ms', 'ta']),
            'field' => fake()->randomElement($fields),
            'value' => fake()->paragraphs(1, true),
            'translation_status' => 'pending',
            'translated_by' => null,
            'reviewed_by' => null,
        ];
    }

    public function translated(): static
    {
        return $this->state(fn (array $attributes) => [
            'translation_status' => 'translated',
            'translated_by' => User::factory(),
        ]);
    }

    public function reviewed(): static
    {
        return $this->state(fn (array $attributes) => [
            'translation_status' => 'reviewed',
            'translated_by' => User::factory(),
            'reviewed_by' => User::factory(),
        ]);
    }
}
