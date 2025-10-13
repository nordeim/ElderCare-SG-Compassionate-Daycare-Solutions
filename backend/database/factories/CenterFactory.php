<?php

namespace Database\Factories;

use App\Models\Center;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Center>
 */
class CenterFactory extends Factory
{
    protected $model = Center::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = $this->generateCenterName();

        return [
            'name' => $name,
            'slug' => Str::slug($name) . '-' . fake()->unique()->numberBetween(1, 9999),
            'short_description' => fake()->sentence(12),
            'description' => fake()->paragraphs(3, true),
            
            // Contact
            'address' => fake()->streetAddress(),
            'city' => fake()->randomElement([
                'Singapore', 'Ang Mo Kio', 'Bedok', 'Bishan', 'Bukit Merah',
                'Clementi', 'Jurong East', 'Tampines', 'Woodlands', 'Yishun'
            ]),
            'postal_code' => fake()->numerify('######'),
            'phone' => '+65' . fake()->numerify('6#######'),
            'email' => fake()->unique()->companyEmail(),
            'website' => fake()->boolean(60) ? fake()->url() : null,
            
            // MOH Compliance
            'moh_license_number' => 'MOH-' . now()->year . '-' . strtoupper(fake()->bothify('??###')),
            'license_expiry_date' => fake()->dateTimeBetween('now', '+3 years')->format('Y-m-d'),
            'accreditation_status' => fake()->randomElement(['accredited', 'pending', 'not_accredited']),
            
            // Operational
            'capacity' => fake()->numberBetween(20, 100),
            'current_occupancy' => 0, // Will be set by state
            'staff_count' => fake()->numberBetween(5, 30),
            'staff_patient_ratio' => fake()->randomFloat(1, 0.5, 2.0),
            
            // JSON fields
            'operating_hours' => $this->generateOperatingHours(),
            'medical_facilities' => $this->generateMedicalFacilities(),
            'amenities' => $this->generateAmenities(),
            'transport_info' => $this->generateTransportInfo(),
            'languages_supported' => ['en', 'zh', 'ms', 'ta'],
            'government_subsidies' => $this->generateSubsidies(),
            
            // Geolocation (Singapore coordinates)
            'latitude' => fake()->latitude(1.2, 1.5),
            'longitude' => fake()->longitude(103.6, 104.0),
            
            // Status
            'status' => 'draft',
            
            // SEO
            'meta_title' => Str::limit($name, 60),
            'meta_description' => Str::limit(fake()->sentence(15), 160),
        ];
    }

    /**
     * Configure the model factory.
     */
    public function configure(): static
    {
        return $this->afterCreating(function (Center $center) {
            // Set realistic occupancy
            $center->update([
                'current_occupancy' => fake()->numberBetween(0, $center->capacity),
            ]);
        });
    }

    /**
     * Indicate a published center.
     */
    public function published(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'published',
        ]);
    }

    /**
     * Indicate an archived center.
     */
    public function archived(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'archived',
        ]);
    }

    /**
     * Indicate a fully occupied center.
     */
    public function fullOccupancy(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'current_occupancy' => $attributes['capacity'],
            ];
        });
    }

    /**
     * Indicate a center with expired license.
     */
    public function expiredLicense(): static
    {
        return $this->state(fn (array $attributes) => [
            'license_expiry_date' => fake()->dateTimeBetween('-1 year', '-1 day')->format('Y-m-d'),
            'accreditation_status' => 'expired',
        ]);
    }

    /**
     * Generate realistic center name.
     */
    protected function generateCenterName(): string
    {
        $prefixes = [
            'Golden Years', 'Silver Age', 'Sunshine', 'Harmony', 'Comfort',
            'Caring Hearts', 'Peaceful Haven', 'Bright Future', 'Happy Days',
            'Evergreen', 'Serene', 'Compassionate', 'Tranquil', 'Joyful'
        ];

        $suffixes = [
            'Care Center', 'Eldercare', 'Senior Care', 'Adult Daycare',
            'Active Aging Center', 'Wellness Center', 'Community Care'
        ];

        return fake()->randomElement($prefixes) . ' ' . fake()->randomElement($suffixes);
    }

    /**
     * Generate operating hours.
     */
    protected function generateOperatingHours(): array
    {
        $hours = [];
        $days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];

        foreach ($days as $day) {
            if (in_array($day, ['saturday', 'sunday']) && fake()->boolean(30)) {
                $hours[$day] = ['closed' => true];
            } else {
                $hours[$day] = [
                    'open' => fake()->randomElement(['07:00', '08:00', '08:30']),
                    'close' => fake()->randomElement(['17:00', '18:00', '19:00']),
                ];
            }
        }

        return $hours;
    }

    /**
     * Generate medical facilities.
     */
    protected function generateMedicalFacilities(): array
    {
        $allFacilities = [
            'examination_room',
            'medication_storage',
            'emergency_equipment',
            'oxygen_supply',
            'defibrillator',
            'first_aid_station',
            'medical_consultation_room'
        ];

        return fake()->randomElements($allFacilities, fake()->numberBetween(3, 6));
    }

    /**
     * Generate amenities.
     */
    protected function generateAmenities(): array
    {
        $allAmenities = [
            'wheelchair_accessible',
            'air_conditioned',
            'wifi',
            'parking',
            'prayer_room',
            'garden',
            'exercise_area',
            'library',
            'TV_lounge',
            'dining_area',
            'nap_room'
        ];

        return fake()->randomElements($allAmenities, fake()->numberBetween(5, 9));
    }

    /**
     * Generate transport information.
     */
    protected function generateTransportInfo(): array
    {
        $mrtStations = [
            'Ang Mo Kio', 'Bedok', 'Bishan', 'Bukit Merah', 'Clementi',
            'Jurong East', 'Tampines', 'Woodlands', 'Yishun', 'Toa Payoh'
        ];

        return [
            'mrt' => [fake()->randomElement($mrtStations)],
            'bus' => fake()->randomElements(['56', '162', '88', '15', '20', '170', '969'], fake()->numberBetween(2, 4)),
            'parking' => fake()->boolean(80),
            'shuttle_service' => fake()->boolean(40),
        ];
    }

    /**
     * Generate government subsidies.
     */
    protected function generateSubsidies(): array
    {
        $subsidies = [
            'pioneer_generation',
            'merdeka_generation',
            'silver_support',
            'community_care',
        ];

        return fake()->randomElements($subsidies, fake()->numberBetween(2, 4));
    }
}
