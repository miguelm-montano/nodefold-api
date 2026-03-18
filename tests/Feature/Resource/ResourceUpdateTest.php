<?php

namespace Tests\Feature\Resource;

use App\Models\User;
use App\Models\Folder;
use App\Models\Resource;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Laravel\Passport\Passport;
use Tests\TestCase;

class ResourceUpdateTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_update_a_resource(): void {

        $user = User::factory()->create();

        Passport::actingAs($user);

        $folder = Folder::factory()->create([
            'user_id' => $user->id
        ]);

        $resource = Resource::factory()->create([
            'user_id' => $user->id,
            'folder_id' => $folder->id,
            'url' => 'https://example.com/image.jpg',
            'type' => 'image',
            'title' => 'Waves',
            'description' => 'Natural photography',
        ]);

        $response = $this->putJson('api/v1/resources/' . $resource->id, [
            'title'       => 'Updated Waves',
            'type'        => 'image',
            'url'         => 'https://example.com/image.jpg',
            'description' => 'Updated description',
        ]);

        $response->assertStatus(200)
                ->assertJsonFragment(['title' => 'Updated Waves']);
        
        $this->assertDatabaseHas('resources', [
            'id'    => $resource->id,
            'title' => 'Updated Waves',
        ]);
    }
}
