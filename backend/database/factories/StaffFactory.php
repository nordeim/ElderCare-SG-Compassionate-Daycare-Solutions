<?php

namespace Database\Factories;

use App\Models\Staff;
use App\Models\Center;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Staff>
 */
class StaffFactory extends Factory
{
    protected $model = Staff::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $position = $this->generatePosition();

        return [
            'center_id' => Center::factory(),
            'name' => fake()->name(),
            'position' => $position,
            'qualifications' => $this->generateQualifications($position),
            'years_of_experience' => fake()->numberBetween(2, 25),
            'bio' => fake()->boolean(70) ? fake()->paragraph(2) : null,
            'photo' => fake()->boolean(60) ? fake()->imageUrl(300, 400, 'people') : null,
            'display_order' => 0,
            'status' => 'active',
        ];
    }

    /**
     * Indicate an inactive staff member.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'inactive',
        ]);
    }

    /**
     * Generate realistic position.
     */
    protected function generatePosition(): string
    {
        return fake()->randomElement([
            'Registered Nurse',
            'Senior Caregiver',
            'Caregiver',
            'Physiotherapist',
            'Occupational Therapist',
            'Activities Coordinator',
            'Medical Director',
            'Center Manager',
            'Social Worker',
        ]);
    }

    /**
     * Generate qualifications based on position.
     */
    protected function generateQualifications(string $position): array
    {
        $baseQualifications = ['First Aid Certified', 'CPR Certified'];

        $positionSpecific = match ($position) {
            'Registered Nurse' => ['Registered Nurse (Singapore)', 'Diploma in Nursing', 'Advanced Cardiac Life Support'],
            'Physiotherapist' => ['Bachelor of Physiotherapy', 'AHPC Registered', 'Geriatric Rehabilitation Specialist'],
            'Occupational Therapist' => ['Bachelor of Occupational Therapy', 'AHPC Registered'],
            'Medical Director' => ['MBBS', 'Geriatric Medicine Specialist', 'SMC Registered'],
            'Senior Caregiver', 'Caregiver' => ['Certified Caregiver', 'WSQ Certificate in Eldercare'],
            default => ['Certificate in Healthcare Support'],
        };

        return array_merge($baseQualifications, $positionSpecific);
    }
}
