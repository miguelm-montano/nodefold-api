<?php

namespace Tests\Feature\Folder;

use App\Models\User;
use App\Models\Folder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Hash;
use Laravel\Passport\Passport;
use Tests\TestCase;

class FolderReadTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_see_their_folders(): void {

        $user = User::factory()->create();

        Passport::actingAs($user);

        $folder = Folder::factory()->create([
            'user_id' => $user->id
        ]);

        $subfolder = Folder::factory()->create([
            'user_id' => $user->id,
            'parent_id' => $folder->id
        ]);

        $response = $this->getJson('api/folders');

        $response->assertStatus(200)
                ->assertJsonFragment(['name' => $folder->name])
                ->assertJsonFragment(['name' => $subfolder->name]);
    }

    public function test_authenticated_user_can_see_one_specific_folder(): void {

        $user = User::factory()->create();

        Passport::actingAs($user);

        $folder = Folder::factory()->create([
            'user_id' => $user->id
        ]);

        $subfolder = Folder::factory()->create([
            'user_id' => $user->id,
            'parent_id' => $folder->id
        ]);

        $response = $this->getJson('api/folders/' . $folder->id);

        $response->assertStatus(200)
                ->assertJsonFragment(['name' => $folder->name])
                ->assertJsonFragment(['name' => $subfolder->name]);
    }
}