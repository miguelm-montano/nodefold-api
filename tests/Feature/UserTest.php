<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Laravel\Passport\Passport;
use Tests\TestCase;

class UserTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_get_their_profile(): void {

        $user = User::factory()->create();

        Passport::actingAs($user);

        $response = $this->getJson('/api/user/me');

        $response->assertStatus(200)
                ->assertJsonFragment([
                    'email' => $user->email,
                    'name' => $user->name,
                ]);
    }

    public function test_authenticated_user_can_update_their_profile(): void {

        $user = User::factory()->create();

        Passport::actingAs($user);

        $response = $this->putJson('/api/user/me', [
            'name' => 'Miguel Updated',
            'email' => 'new@test.com',
        ]);

        $response->assertStatus(200)
                ->assertJsonFragment([
                    'name' => 'Miguel Updated',
                    'email' => 'new@test.com',
                ]);
    }

    public function test_authenticated_user_can_delete_their_profile(): void {

        $user = User::factory()->create();

        Passport::actingAs($user);

        $response = $this->deleteJson('/api/user/me');

        $response->assertStatus(200)
                ->assertJsonFragment([
                    'message' => 'User deleted successfully'
                ]);
        
        $this->assertDatabaseMissing('users', [
            'id' => $user->id
        ]);
    }
}
