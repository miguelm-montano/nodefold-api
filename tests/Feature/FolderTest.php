<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Folder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Hash;
use Laravel\Passport\Passport;
use Tests\TestCase;

class FolderTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_create_a_folder(): void {
        $user = User::factory()->create();

        Passport::actingAs($user);

        $response = $this->postJson('/api/folders', [
        'name' => 'Design'
    ]);

        $response->assertStatus(201)
                ->assertJsonFragment(['name' => 'Design']);

        $this->assertDatabaseHas('folders', ['name' => 'Design']);
    }
}
