<?php

namespace Database\Factories;

use App\Models\Service;
use App\Models\Center;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Service>
 */
class ServiceFactory extends Factory
{
    protected $model = Service::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = $this->generateServiceName();
        $hasPrice = fake()->boolean(80); // 80% have prices

        return [
            'center_id' => Center::factory(),
            'name' => $name,
            'slug' => Str::slug($name),
            'description' => fake()->paragraphs(2, true),
            'price' => $hasPrice ? fake()->randomFloat(2, 50, 300) : null,
            'price_unit' => $hasPrice ? fake()->randomElement(['hour', 'day', 'week', 'month']) : null,
            'duration' => fake()->randomElement(['2 hours', '4 hours', 'Half day', 'Full day', '8 hours']),
            'features' => $this->generateServiceFeatures(),
            'status' => 'draft',
            'display_order' => 0,
        ];
    }

    /**
     * Indicate a published service.
     */
    public function published(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'published',
        ]);
    }

    /**
     * Indicate a service with no price (POA).
     */
    public function priceOnApplication(): static
    {
        return $this->state(fn (array $attributes) => [
            'price' => null,
            'price_unit' => null,
        ]);
    }

    /**
     * Generate realistic service name.
     */
    protected function generateServiceName(): string
    {
        $services = [
            'Full Day Care',
            'Half Day Care',
            'Respite Care',
            'Dementia Care',
            'Nursing Care',
            'Physiotherapy Session',
            'Occupational Therapy',
            'Social Activities Program',
            'Meals & Nutrition',
            'Personal Care Assistance',
            'Medical Supervision',
            'Exercise & Wellness Program',
        ];

        return fake()->randomElement($services);
    }

    /**
     * Generate service features.
     */
    protected function generateServiceFeatures(): array
    {
        $allFeatures = [
            'meals_included',
            'medication_management',
            'physiotherapy',
            'occupational_therapy',
            'social_activities',
            'transport_service',
            'medical_monitoring',
            'bathing_assistance',
            'exercise_program',
            'mental_stimulation',
            'emergency_response',
        ];

        return fake()->randomElements($allFeatures, fake()->numberBetween(3, 7));
    }
}
