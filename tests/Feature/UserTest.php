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
}
