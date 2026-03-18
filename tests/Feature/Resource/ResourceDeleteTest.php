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

class ResourceDeleteTest extends TestCase
{
   use RefreshDatabase;

   public function test_authenticated_user_can_delete_a_resource(): void {

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

        $response = $this->deleteJson('api/v1/resources/' . $resource->id);

        $response->assertStatus(200)
                ->assertJsonFragment([
                    'message' => 'Resource deleted'
                ]);
        
        $this->assertDatabaseMissing('resources' , [
            'id' => $resource->id
        ]);
   }
}
