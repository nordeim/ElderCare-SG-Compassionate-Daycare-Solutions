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
