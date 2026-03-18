<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Laravel\Passport\Passport;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void {
        
        parent::setUp();
        \Laravel\Passport\Client::create([
            'name' => 'Test Personal Access Client',
            'secret' => null,
            'provider' => 'users',
            'redirect_uris' => [],
            'grant_types' => ['personal_access'],
            'revoked' => false,
        ]);
}

    public function test_user_can_register(): void {

        $response = $this->postJson('/api/v1/register', [
            'name' => 'Test',
            'email' => 'test@test.com',
            'password' => 'Password123',
            'password_confirmation' => 'Password123'
        ]);

        $response->assertStatus(201)
                ->assertJsonStructure([
                    'user',
                    'token'
                ]);

        $this->assertDatabaseHas('users', [
            'email' => 'test@test.com'
        ]);
    }

    public function test_user_cannot_register_with_existing_email(): void {

        User::factory()->create([
            'email' => 'test@test.com'
        ]);

        $response = $this->postJson('/api/v1/register', [
            'name' => 'Test',
            'email' => 'test@test.com',
            'password' => 'Password123',
            'password_confirmation' => 'Password123'
        ]);

        $response->assertStatus(422);
    }

    public function test_user_can_login(): void {

        $user = User::factory()->create([
            'password' => Hash::make('Password123')
        ]);

        $response = $this->postJson('/api/v1/login', [
            'email' => $user->email,
            'password' => 'Password123'
        ]);

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'user',
                    'token'
                ]);
    }

    public function test_user_cannot_login_with_invalid_credentials(): void {

        $user = User::factory()->create([
            'password' => Hash::make('Password123'),
        ]);

        $response = $this->postJson('/api/v1/login', [
            'email'    => $user->email,
            'password' => 'wrongpassword',
        ]);

        $response->assertStatus(401)
                ->assertJsonFragment(['message' => 'Invalid credentials']);
    }

    public function test_user_can_logout(): void {

        $user = User::factory()->create();

        Passport::actingAs($user);

        $response = $this->postJson('/api/v1/logout');

        $response->assertStatus(200)
                ->assertJsonFragment(['message' => 'Logged out successfully']);
    }
}
