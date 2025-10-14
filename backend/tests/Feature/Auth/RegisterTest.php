<?php

namespace Tests\Feature\Auth;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegisterTest extends TestCase
{
    use RefreshDatabase;

    public function test_register_returns_token_and_user()
    {
        $payload = [
            'name' => 'Test User',
            'email' => 'testuser@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ];

        $resp = $this->postJson('/api/v1/auth/register', $payload);

        $resp->assertStatus(201);
        $resp->assertJsonStructure(['user' => ['id', 'email'], 'token']);
        $this->assertDatabaseHas('users', ['email' => 'testuser@example.com']);
    }
}
