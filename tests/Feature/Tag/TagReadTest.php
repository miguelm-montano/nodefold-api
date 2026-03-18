<?php

namespace Tests\Feature\Tag;

use App\Models\User;
use App\Models\Folder;
use App\Models\Resource;
use App\Models\Tag;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Laravel\Passport\Passport;
use Tests\TestCase;

class TagReadTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_see_all_his_tagged_resources(): void {

        $user = User::factory()->create();

        Passport::actingAs($user);

        $tag = Tag::factory()->create([
            'name' => 'Test',
            'user_id' => $user->id
        ]);

        $response = $this->getJson('/api/v1/tags');

        $response->assertStatus(200)
             ->assertJsonFragment(['name' => 'Test']);
    }

    public function test_authenticated_user_can_filter_resources_by_untagged(): void {

        $user = User::factory()->create();

        Passport::actingAs($user);

        $folder = Folder::factory()->create([
            'user_id' => $user->id
        ]);

        $untaggedResource = Resource::factory()->create([
            'user_id' => $user->id,
            'folder_id' => $folder->id,
            'title' => 'Untagged Resource',
            'type' => 'image',
            'url' => 'https://example.com/image.jpg',
        ]);

        $response = $this->getJson('api/v1/resources?tagged=false');

        $response->assertStatus(200)
                ->assertJsonFragment(['title' => 'Untagged Resource']);
        
    }

    public function test_authenticated_user_can_filter_resources_by_all_tagged(): void {

        $user = User::factory()->create();

        Passport::actingAs($user);

        $folder = Folder::factory()->create([
            'user_id' => $user->id,
        ]);

        $tag = Tag::factory()->create([
            'name' => 'Test',
            'user_id' => $user->id
        ]);

        $taggedResource = Resource:: factory()->create([
            'user_id' => $user->id,
            'folder_id' => $folder->id,
            'title' => 'Tagged Resource',
            'type' => 'image',
            'url' => 'https://example.com/image.jpg',
        ]);

        $taggedResource->tags()->attach($tag->id);

        $response = $this->getJson('/api/v1/resources?tagged=true');

        $response->assertStatus(200)
                ->assertJsonFragment(['title' => 'Tagged Resource']);
    }

    public function test_authenticated_user_can_see_tags_of_a_resource(): void {

        $user = User::factory()->create();

        Passport::actingAs($user);

        $folder = Folder::factory()->create([
            'user_id' => $user->id
        ]);

        $resource = Resource::factory()->create([
            'user_id'   => $user->id,
            'folder_id' => $folder->id,
            'type'      => 'image',
            'url'       => 'https://example.com/image.jpg',
            'title'     => 'Waves',
        ]);

        $tag = Tag::factory()->create([
            'user_id' => $user->id,
            'name'    => 'ocean'
        ]);

        $resource->tags()->attach($tag->id);

        $response = $this->getJson('api/v1/resources/' . $resource->id);

        $response->assertStatus(200)
            ->assertJsonFragment(['name' => 'ocean']);
    }

    public function test_authenticated_user_can_search_resource_by_tag_name(): void {

        $user = User::factory()->create();

        Passport::actingAs($user);

        $folder = Folder::factory()->create([
            'user_id' => $user->id
        ]);

        $tag = Tag::factory()->create([
            'name' => 'Test',
            'user_id' => $user->id
        ]);

        $taggedResource = Resource:: factory()->create([
            'user_id' => $user->id,
            'folder_id' => $folder->id,
            'title' => 'Tagged Resource',
            'type' => 'image',
            'url' => 'https://example.com/image.jpg',
        ]);

        $taggedResource->tags()->attach($tag->id);

        $untaggedResource = Resource::factory()->create([
            'user_id'   => $user->id,
            'folder_id' => $folder->id,
            'title'     => 'Untagged Resource',
            'type'      => 'image',
            'url'       => 'https://example.com/image.jpg',
        ]);

        $response = $this->getJson('/api/v1/resources?tag=Test');

        $response->assertStatus(200)
                ->assertJsonFragment(['title' => 'Tagged Resource'])
                ->assertJsonMissing(['title' => 'Untagged Resource']);
    }

    public function test_authenticated_user_can_update_tags_of_a_resource(): void {

        $user = User::factory()->create();

        Passport::actingAs($user);

        $folder = Folder::factory()->create(['user_id' => $user->id]);

        $resource = Resource::factory()->create([
            'user_id'   => $user->id,
            'folder_id' => $folder->id,
            'type'      => 'image',
            'url'       => 'https://example.com/image.jpg',
            'title'     => 'Waves',
        ]);

        $response = $this->putJson('api/v1/resources/' . $resource->id, [
            'title' => 'Waves',
            'type'  => 'image',
            'url'   => 'https://example.com/image.jpg',
            'tags'  => 'ocean, nature',
        ]);

        $response->assertStatus(200)
            ->assertJsonFragment(['name' => 'ocean']);

        $this->assertDatabaseHas('tags', ['name' => 'ocean', 'user_id' => $user->id]);
    }

}
