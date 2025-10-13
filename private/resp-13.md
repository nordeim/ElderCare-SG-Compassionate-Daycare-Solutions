# Day 6: Factories — Complete Implementation (14 Files)

**Objective**: Create comprehensive, realistic factories for all models to support testing and seeding.

---

## Factories (14 files)

### 1. `backend/database/factories/UserFactory.php`

```php
<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = User::class;

    /**
     * The current password being used by the factory.
     */
    protected static ?string $password = null;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'phone' => $this->generateSingaporePhone(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'role' => 'user',
            'preferred_language' => fake()->randomElement(['en', 'zh', 'ms', 'ta']),
            'remember_token' => Str::random(10),
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }

    /**
     * Indicate that the user is an admin.
     */
    public function admin(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => 'admin',
        ]);
    }

    /**
     * Indicate that the user is a super admin.
     */
    public function superAdmin(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => 'super_admin',
        ]);
    }

    /**
     * Indicate that the user prefers English.
     */
    public function english(): static
    {
        return $this->state(fn (array $attributes) => [
            'preferred_language' => 'en',
        ]);
    }

    /**
     * Indicate that the user prefers Mandarin.
     */
    public function mandarin(): static
    {
        return $this->state(fn (array $attributes) => [
            'preferred_language' => 'zh',
        ]);
    }

    /**
     * Generate a realistic Singapore phone number.
     */
    protected function generateSingaporePhone(): string
    {
        // Singapore mobile: +65 followed by 8 or 9, then 7 digits
        $prefix = fake()->randomElement(['8', '9']);
        $number = $prefix . fake()->numerify('#######');
        
        return '+65' . $number;
    }

    /**
     * Configure the model factory.
     */
    public function configure(): static
    {
        return $this->afterCreating(function (User $user) {
            // Automatically create a profile for each user
            if (!$user->profile) {
                \App\Models\Profile::factory()->create(['user_id' => $user->id]);
            }
        });
    }
}
```

---

### 2. `backend/database/factories/ProfileFactory.php`

```php
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
```

---

### 3. `backend/database/factories/ConsentFactory.php`

```php
<?php

namespace Database\Factories;

use App\Models\Consent;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Consent>
 */
class ConsentFactory extends Factory
{
    protected $model = Consent::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $type = fake()->randomElement([
            'account',
            'marketing_email',
            'marketing_sms',
            'analytics_cookies',
            'functional_cookies'
        ]);

        return [
            'user_id' => User::factory(),
            'consent_type' => $type,
            'consent_given' => true,
            'consent_text' => $this->getConsentText($type),
            'consent_version' => '1.0',
            'ip_address' => fake()->ipv4(),
            'user_agent' => fake()->userAgent(),
        ];
    }

    /**
     * Indicate that consent was given.
     */
    public function given(): static
    {
        return $this->state(fn (array $attributes) => [
            'consent_given' => true,
        ]);
    }

    /**
     * Indicate that consent was withdrawn.
     */
    public function withdrawn(): static
    {
        return $this->state(fn (array $attributes) => [
            'consent_given' => false,
            'consent_text' => 'Consent withdrawn by user',
        ]);
    }

    /**
     * Indicate account consent.
     */
    public function account(): static
    {
        return $this->state(fn (array $attributes) => [
            'consent_type' => 'account',
            'consent_text' => $this->getConsentText('account'),
        ]);
    }

    /**
     * Indicate marketing email consent.
     */
    public function marketingEmail(): static
    {
        return $this->state(fn (array $attributes) => [
            'consent_type' => 'marketing_email',
            'consent_text' => $this->getConsentText('marketing_email'),
        ]);
    }

    /**
     * Get consent text based on type.
     */
    protected function getConsentText(string $type): string
    {
        $texts = [
            'account' => 'I agree to create an account and accept the terms of service and privacy policy.',
            'marketing_email' => 'I agree to receive marketing and promotional emails from ElderCare SG.',
            'marketing_sms' => 'I agree to receive marketing and promotional SMS messages from ElderCare SG.',
            'analytics_cookies' => 'I agree to the use of analytics cookies to improve my experience.',
            'functional_cookies' => 'I agree to the use of functional cookies necessary for the site to work.',
        ];

        return $texts[$type] ?? 'I consent to this action.';
    }
}
```

---

### 4. `backend/database/factories/AuditLogFactory.php`

```php
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
```

---

### 5. `backend/database/factories/CenterFactory.php`

```php
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
```

---

### 6. `backend/database/factories/ServiceFactory.php`

```php
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
```

---

### 7. `backend/database/factories/StaffFactory.php`

```php
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
```

---

### 8. `backend/database/factories/BookingFactory.php`

```php
<?php

namespace Database\Factories;

use App\Models\Booking;
use App\Models\User;
use App\Models\Center;
use App\Models\Service;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Booking>
 */
class BookingFactory extends Factory
{
    protected $model = Booking::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $bookingDate = fake()->dateTimeBetween('now', '+3 months');

        return [
            'booking_number' => $this->generateBookingNumber(),
            'user_id' => User::factory(),
            'center_id' => Center::factory(),
            'service_id' => fake()->boolean(70) ? Service::factory() : null,
            'booking_date' => $bookingDate->format('Y-m-d'),
            'booking_time' => fake()->randomElement(['09:00:00', '10:00:00', '11:00:00', '14:00:00', '15:00:00', '16:00:00']),
            'booking_type' => fake()->randomElement(['visit', 'consultation', 'trial_day']),
            'questionnaire_responses' => $this->generateQuestionnaire(),
            'status' => 'pending',
            'calendly_event_id' => null,
            'calendly_event_uri' => null,
            'calendly_cancel_url' => null,
            'calendly_reschedule_url' => null,
            'cancellation_reason' => null,
            'notes' => fake()->boolean(20) ? fake()->sentence() : null,
            'confirmation_sent_at' => null,
            'reminder_sent_at' => null,
            'sms_sent' => false,
        ];
    }

    /**
     * Indicate a confirmed booking.
     */
    public function confirmed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'confirmed',
            'confirmation_sent_at' => now()->subHours(fake()->numberBetween(1, 48)),
        ]);
    }

    /**
     * Indicate a completed booking.
     */
    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'booking_date' => fake()->dateTimeBetween('-3 months', '-1 day')->format('Y-m-d'),
            'status' => 'completed',
            'confirmation_sent_at' => now()->subDays(fake()->numberBetween(7, 90)),
            'reminder_sent_at' => now()->subDays(fake()->numberBetween(1, 90)),
        ]);
    }

    /**
     * Indicate a cancelled booking.
     */
    public function cancelled(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'cancelled',
            'cancellation_reason' => fake()->randomElement([
                'Schedule conflict - need to reschedule',
                'Found alternative care arrangement',
                'Family member available to provide care',
                'Medical appointment conflict',
            ]),
        ]);
    }

    /**
     * Indicate booking with Calendly integration.
     */
    public function withCalendly(): static
    {
        return $this->state(function (array $attributes) {
            $eventId = 'evt_' . fake()->uuid();
            
            return [
                'calendly_event_id' => $eventId,
                'calendly_event_uri' => "https://api.calendly.com/scheduled_events/{$eventId}",
                'calendly_cancel_url' => "https://calendly.com/cancellations/{$eventId}",
                'calendly_reschedule_url' => "https://calendly.com/reschedulings/{$eventId}",
            ];
        });
    }

    /**
     * Generate unique booking number.
     */
    protected function generateBookingNumber(): string
    {
        $date = now()->format('Ymd');
        $sequence = str_pad(fake()->unique()->numberBetween(1, 9999), 4, '0', STR_PAD_LEFT);
        
        return "BK-{$date}-{$sequence}";
    }

    /**
     * Generate realistic questionnaire responses.
     */
    protected function generateQuestionnaire(): array
    {
        return [
            'elderly_age' => fake()->numberBetween(65, 95),
            'medical_conditions' => fake()->randomElements([
                'diabetes',
                'hypertension',
                'heart_disease',
                'dementia',
                'arthritis',
                'osteoporosis',
                'stroke_history',
            ], fake()->numberBetween(0, 3)),
            'mobility' => fake()->randomElement(['independent', 'walker', 'wheelchair', 'bedridden']),
            'special_requirements' => fake()->boolean(60) ? fake()->sentence(10) : null,
            'dietary_restrictions' => fake()->boolean(40) ? fake()->randomElements([
                'halal', 'vegetarian', 'diabetic', 'low_sodium', 'texture_modified'
            ], fake()->numberBetween(1, 2)) : [],
        ];
    }
}
```

---

### 9. `backend/database/factories/TestimonialFactory.php`

```php
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
            'rating' => fake()->numberBetween(3, 5), // Bias toward positive reviews
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
                'moderated_at' => now()->subHours(fake()->numberBetween(1
```                