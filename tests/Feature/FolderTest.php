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

    public function test_authenticated_user_can_create_a_subfolder(): void {

        $user = User::factory()->create();

        Passport::ActingAs($user);

        $folder = Folder::factory()->create([
            'user_id' => $user->id
        ]);

        $response = $this->postJson('api/folders/' . $folder->id . '/folders', [
            'name' => 'Logos'
        ]);

        $response->assertStatus(201)
                ->assertJsonFragment(['name' => 'Logos']);

        $this->assertDatabaseHas('folders', [
            'name' => 'Logos',
            'parent_id' => $folder->id
        ]);
    }
}