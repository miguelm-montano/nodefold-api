<?php

namespace Tests\Feature\Folder;

use App\Models\User;
use App\Models\Folder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Hash;
use Laravel\Passport\Passport;
use Tests\TestCase;

class FolderCreateTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_create_a_folder(): void {
        $user = User::factory()->create();

        Passport::actingAs($user);

        $response = $this->postJson('/api/v1/folders', [
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

        $response = $this->postJson('/api/v1/folders', [
            'name'      => 'Logos',
            'parent_id' => $folder->id
        ]);

        $response->assertStatus(201)
                ->assertJsonFragment(['name' => 'Logos']);

        $this->assertDatabaseHas('folders', [
            'name' => 'Logos',
            'parent_id' => $folder->id
        ]);
    }

    public function test_auhthenticated_user_cannot_create_a_subfolder_inside_another_subfolder(): void {

        $user = User::factory()->create();

        Passport::actingAs($user);

        $folder = Folder::factory()->create([
            'user_id' => $user->id
        ]);

        $subfolder = Folder::factory()->create([
            'user_id' => $user->id,
            'parent_id' => $folder->id
        ]);

        $response = $this->postJson('/api/v1/folders', [
            'name'      => 'Photos',
            'parent_id' => $subfolder->id
        ]);

        $response->assertStatus(403);
    }

    public function test_a_folder_cannot_be_created_without_name(): void {

        $user = User::factory()->create();

        Passport::actingAs($user);

        $response = $this->postJson('/api/v1/folders', [
            'name' => null
        ]);

        $response->assertStatus(422);
    }

    public function test_authenticated_user_cannot_create_a_subfolder_in_a_folders_of_other(): void {

        $user = User::factory()->create();

        $otherUser = User::factory()->create();

        Passport::actingAs($user);
        $folder = Folder::factory()->create([
            'user_id' => $user->id
        ]);

        Passport::actingAs($otherUser);

        $response = $this->postJson('/api/v1/folders', [
            'name'      => 'Explode',
            'parent_id' => $folder->id
        ]);

        $response->assertStatus(404);
    }
}