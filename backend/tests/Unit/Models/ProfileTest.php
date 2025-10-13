<?php

namespace Tests\Unit\Models;

use App\Models\Profile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_belongs_to_user()
    {
        $user = User::factory()->create();
        $profile = Profile::factory()->create(['user_id' => $user->id]);
        
        $this->assertInstanceOf(User::class, $profile->user);
        $this->assertEquals($user->id, $profile->user->id);
    }

    /** @test */
    public function it_casts_birth_date_to_date()
    {
        $profile = Profile::factory()->create(['birth_date' => '1950-01-15']);
        
        $this->assertInstanceOf(\Carbon\Carbon::class, $profile->birth_date);
        $this->assertEquals('1950-01-15', $profile->birth_date->toDateString());
    }

    /** @test */
    public function it_stores_address_information()
    {
        $profile = Profile::factory()->create([
            'address' => '123 Orchard Road',
            'city' => 'Singapore',
            'postal_code' => '238858',
        ]);
        
        $this->assertEquals('123 Orchard Road', $profile->address);
        $this->assertEquals('238858', $profile->postal_code);
    }
}
