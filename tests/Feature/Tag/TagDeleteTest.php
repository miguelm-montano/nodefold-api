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

class TagDeleteTest extends TestCase
{
    use RefreshDatabase;

    public function test_deleting_a_resource_also_deletes_orphan_tags(): void {

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

        $tag = Tag::factory()->create(['user_id' => $user->id, 'name' => 'ocean']);

        $resource->tags()->attach($tag->id);

        $this->deleteJson('api/v1/resources/' . $resource->id);

        $this->assertDatabaseMissing('tags', ['id' => $tag->id]);
    }

    public function test_deleting_a_resource_does_not_delete_tags_used_by_other_resources(): void {

        $user = User::factory()->create();

        Passport::actingAs($user);

        $folder = Folder::factory()->create([
            'user_id' => $user->id
        ]);

        $resource1 = Resource::factory()->create([
            'user_id' => $user->id,
            'folder_id' => $folder->id,
            'type' => 'image',
            'url' => 'https://example.com/1.jpg',
            'title' => 'Waves'
        ]);

        $resource2 = Resource::factory()->create([
            'user_id' => $user->id,
            'folder_id' => $folder->id,
            'type' => 'image',
            'url' => 'https://example.com/2.jpg',
            'title' => 'Mountains',
        ]);

        $tag = Tag::factory()->create([
            'user_id' => $user->id,
            'name' => 'ocean'
        ]);
    
        $resource1->tags()->attach($tag->id);
        $resource2->tags()->attach($tag->id);

        $this->deleteJson('api/v1/resources/' . $resource1->id);

        $this->assertDatabaseHas('tags', ['id' => $tag->id]);
    }

}
