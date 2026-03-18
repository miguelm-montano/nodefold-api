<?php

namespace Tests\Feature\Error;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Passport\Passport;
use Tests\TestCase;

class ErrorHandlingTest extends TestCase
{
    use RefreshDatabase;

    public function test_returns_json_404_when_route_not_found(): void {

        $response = $this->getJson('/api/v1/nonexistent');

        $response->assertStatus(404)
                ->assertJsonFragment(['message' => 'Route not found']);
    }

    public function test_returns_json_401_when_unauthenticated(): void {

        $response = $this->getJson('/api/v1/folders');

        $response->assertStatus(401)
                ->assertJsonFragment(['message' => 'Unauthenticated']);
    }
}
