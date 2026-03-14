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

class ResourceReadTest extends TestCase
{

    use RefreshDatabase;

    public function test_authenticated_user_can_see_all_their_resources(): void {

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

        $response = $this->getJson('api/resources');

        $response->assertStatus(200)
                ->assertJsonFragment(['title' => 'Waves']);
    }

    public function test_authenticated_user_can_see_an_specific_resource(): void {

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

        $response = $this->getJson('api/resources/' . $resource->id);

        $response->assertStatus(200)
                ->assertJsonFragment(['title' => 'Waves']);
    }
}
