<?php

namespace Tests\Feature\Folder;

use App\Models\User;
use App\Models\Folder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Hash;
use Laravel\Passport\Passport;
use Tests\TestCase;

class FolderUpdateTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_edit_the_name_of_the_folder(): void {

        $user = User::factory()->create();

        Passport::actingAs($user);

        $folder = Folder::factory()->create([
            'user_id' => $user->id
        ]);

        $response = $this->putJson('api/folders/' . $folder->id, ['name' => 'New name']);

        $response->assertStatus(200)
                ->assertJsonFragment(['name' => 'New name']);
    }
}
