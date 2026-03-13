<?php

namespace Tests\Feature\Folder;

use App\Models\User;
use App\Models\Folder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Hash;
use Laravel\Passport\Passport;
use Tests\TestCase;

class FolderDeleteTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_delete_a_folder(): void {

        $user = User::factory()->create();

        Passport::actingAs($user);

        $folder = Folder::factory()->create([
            'user_id' => $user->id
        ]);

        $response = $this->deleteJson('api/folders/' . $folder->id);

        $response->assertStatus(200)
                ->assertJsonFragment([
                    'message' => 'Folder deleted'
                ]);

        $this->assertDatabaseMissing('folders', [
            'id' => $folder->id
        ]);
    }

    public function test_deleting_a_parent_folder_also_deletes_its_subfolders():void {

        $user = User::factory()->create();

        Passport::actingAs($user);

        $folder = Folder::factory()->create([
            'user_id' => $user->id
        ]);

        $subfolder = Folder::factory()->create([
            'user_id' => $user->id,
            'parent_id' => $folder->id
        ]);

        $this->deleteJson('api/folders/' . $folder->id);

        $this->assertDatabaseMissing(
            'folders', ['id' => $subfolder->id
        ]);


    }
}
