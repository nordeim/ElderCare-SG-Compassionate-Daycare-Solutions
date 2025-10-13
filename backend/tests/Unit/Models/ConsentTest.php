<?php

namespace Tests\Unit\Models;

use App\Models\Consent;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ConsentTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_belongs_to_user()
    {
        $user = User::factory()->create();
        $consent = Consent::factory()->create(['user_id' => $user->id]);
        
        $this->assertInstanceOf(User::class, $consent->user);
    }

    /** @test */
    public function it_has_consent_types()
    {
        $types = ['account', 'marketing_email', 'marketing_sms', 'analytics_cookies', 'functional_cookies'];
        
        foreach ($types as $type) {
            $consent = Consent::factory()->create(['consent_type' => $type]);
            $this->assertEquals($type, $consent->consent_type);
        }
    }

    /** @test */
    public function it_tracks_ip_and_user_agent()
    {
        $consent = Consent::factory()->create([
            'ip_address' => '192.168.1.1',
            'user_agent' => 'Mozilla/5.0',
        ]);
        
        $this->assertEquals('192.168.1.1', $consent->ip_address);
        $this->assertNotNull($consent->user_agent);
    }

    /** @test */
    public function it_stores_consent_snapshot()
    {
        $consent = Consent::factory()->create([
            'consent_text' => 'I agree to the privacy policy version 1.0',
            'consent_version' => '1.0',
        ]);
        
        $this->assertStringContainsString('privacy policy', $consent->consent_text);
        $this->assertEquals('1.0', $consent->consent_version);
    }
}
