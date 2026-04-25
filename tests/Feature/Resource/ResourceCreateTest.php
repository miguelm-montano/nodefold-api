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

        $response = $this->postJson('/api/v1/folders/' . $folder->id .'/resources/', [
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

        $response = $this->postJson('/api/v1/folders/' . $folder->id .'/resources/', [
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

    public function test_authenticated_user_can_upload_a_color_palette(): void {

        $user = User::factory()->create();

        Passport::actingAs($user);

        $folder = Folder::factory()->create([
            'user_id' => $user->id
        ]);

        $response = $this->postJson('/api/v1/folders/' . $folder->id .'/resources/', [
            'image' => null,
            'url' => 'https://coolors.co/palette/dad7cd-a3b18a-588157-3a5a40-344e41',
            'type' => 'color_palette',
            'title' => 'Color test',
            'description' => 'test',
            'tags' => 'greens',
        ]);

        $response->assertStatus(201)
                ->assertJsonFragment(['title' => 'Color test'])
                ->assertJsonPath('color_data.0', 'dad7cd');

        $this->assertDatabaseHas('resources', [
            'title'  => 'Color test',
            'folder_id' => $folder->id,
        ]);
    }

    public function test_authenticated_user_can_add_a_resource_with_image_url(): void {

        $user = User::factory()->create();
        Passport::actingAs($user);

        $folder = Folder::factory()->create(['user_id' => $user->id]);

        $response = $this->postJson('/api/v1/folders/' . $folder->id . '/resources', [
            'url' => 'https://example.com/image.jpg',
            'type' => 'image',
            'title' => 'External Image',
        ]);

        $response->assertStatus(201)
            ->assertJsonFragment(['title' => 'External Image']);
    }

    public function test_url_with_javascript_scheme_is_rejected(): void {

        $user = User::factory()->create();
        Passport::actingAs($user);
        $folder = Folder::factory()->create(['user_id' => $user->id]);

        $response = $this->postJson('/api/v1/folders/' . $folder->id . '/resources', [
            'url' => 'javascript:alert(1)',
            'type' => 'web',
            'title' => 'Malicious',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['url']);
    }

    public function test_url_with_file_scheme_is_rejected(): void {

        $user = User::factory()->create();
        Passport::actingAs($user);
        $folder = Folder::factory()->create(['user_id' => $user->id]);

        $response = $this->postJson('/api/v1/folders/' . $folder->id . '/resources', [
            'url' => 'file:///etc/passwd',
            'type' => 'web',
            'title' => 'Malicious',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['url']);
    }

    public function test_url_exceeding_max_length_is_rejected(): void {

        $user = User::factory()->create();
        Passport::actingAs($user);
        $folder = Folder::factory()->create(['user_id' => $user->id]);

        $response = $this->postJson('/api/v1/folders/' . $folder->id . '/resources', [
            'url' => 'https://example.com/' . str_repeat('a', 2048),
            'type' => 'web',
            'title' => 'Long URL',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['url']);
    }

    public function test_image_upload_with_php_file_disguised_as_jpg_is_rejected(): void {

        $user = User::factory()->create();
        Passport::actingAs($user);
        $folder = Folder::factory()->create(['user_id' => $user->id]);

        $file = UploadedFile::fake()->createWithContent('shell.jpg', '<?php system($_GET["cmd"]); ?>');

        $response = $this->postJson('/api/v1/folders/' . $folder->id . '/resources', [
            'image' => $file,
            'type' => 'image',
            'title' => 'Shell',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['image']);
    }

    public function test_tag_exceeding_30_chars_is_rejected(): void {

        $user = User::factory()->create();
        Passport::actingAs($user);
        $folder = Folder::factory()->create(['user_id' => $user->id]);

        $response = $this->postJson('/api/v1/folders/' . $folder->id . '/resources', [
            'url' => 'https://example.com/image.jpg',
            'type' => 'image',
            'title' => 'Tagged Resource',
            'tags' => str_repeat('a', 31),
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['tags']);
    }

    public function test_multiple_valid_tags_are_accepted(): void {

        $user = User::factory()->create();
        Passport::actingAs($user);
        $folder = Folder::factory()->create(['user_id' => $user->id]);

        $response = $this->postJson('/api/v1/folders/' . $folder->id . '/resources', [
            'url' => 'https://example.com/image.jpg',
            'type' => 'image',
            'title' => 'Tagged Resource',
            'tags' => 'nature, ocean, blue',
        ]);

        $response->assertStatus(201);
    }

    public function test_tags_with_unicode_characters_are_accepted(): void {

        $user = User::factory()->create();
        Passport::actingAs($user);
        $folder = Folder::factory()->create(['user_id' => $user->id]);

        $response = $this->postJson('/api/v1/folders/' . $folder->id . '/resources', [
            'url' => 'https://example.com/image.jpg',
            'type' => 'image',
            'title' => 'Tagged Resource',
            'tags' => 'diseño, ilustración',
        ]);

        $response->assertStatus(201);
    }
}
