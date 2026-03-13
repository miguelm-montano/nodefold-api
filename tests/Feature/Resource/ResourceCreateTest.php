<?php

namespace Tests\Feature\Resource;

use App\Models\User;
use App\Models\Folder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Laravel\Passport\Passport;
use Tests\TestCase;

class ResourceCreateTest extends TestCase
{

    use RefreshDatabase; 

    public function test_aunthenticated_user_can_add_a_resource(): void {

        $user = User::factory()->create();

        Passport::actingAs($user);

        $folder = Folder::factory()->create([
            'user_id' => $user->id 
        ]);

        $response = $this->postJson('/api/folders/' . $folder->id .'/resources/', [
            'url' => 'https://example.com/image.jpg',
            'type' => 'image',
            'title' => 'Waves',
            'description' => 'Natural photography',
            'tags' => 'natural, ocean'
        ]);

        $response->assertStatus(201)
                ->assertJsonFragment(['title' => 'Waves'
                ]);

        $this->assertDatabaseHas('resources', [
            'title'     => 'Waves',
            'folder_id' => $folder->id,
        ]);
    }

    public function test_auhthenticated_user_can_upload_an_image(): void {

        $user = User::factory()->create();

        Passport::actingAs($user);

        $folder = Folder::factory()->create([
            'user_id' => $user->id
        ]);

        $image = UploadedFile::fake()->image('waves.jpg');

        $response = $this->postJson('/api/folders/' . $folder->id .'/resources/', [
            'image' => $image,
            'url' => null,
            'type' => 'image',
            'title' => 'Waves',
            'description' => 'Natural photography',
            'tags' => 'natural, ocean',

        ]);

        Storage::fake('public');

        $this->assertDatabaseHas('resources', [
            'title'  => 'Waves',
            'folder_id' => $folder->id,
        ]);
    }
}
