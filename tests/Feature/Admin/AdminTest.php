<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use App\Models\Folder;
use App\Models\Resource;
use App\Models\Tag;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Passport\Passport;
use SebastianBergmann\FileIterator\Factory;
use Tests\TestCase;

class AdminTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_see_all_users(): void {

        $admin = User::factory()->create([
            'role' => 'admin'
        ]);

        User::factory()->count(3)->create();

        Passport::actingAs($admin);

        $response = $this->getJson(('/api/v1/admin/users'));

        $response->assertStatus(200)
                ->assertJsonCount(4);
    }

    public function test_non_admin_cannot_access_admin_users(): void {

        $user = User::factory()->create();

        Passport::actingAs($user);

        $response = $this->getJson('/api/v1/admin/users');

        $response->assertStatus(403);
    }

    public function test_admin_can_delete_a_user(): void {

        $admin = User::factory()->create([
            'role' => 'admin'
        ]);

        $user = User::factory()->create();

        Passport::actingAs($admin);

        $response = $this->deleteJson('/api/v1/admin/users/' . $user->id);

        $response->assertStatus(200)
                ->assertJsonFragment(['message' => 'User deleted successfully']);

        $this->assertDatabaseMissing('users', ['id' => $user->id]);
    }

    public function test_non_admin_cannot_delete_a_user(): void {

        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        Passport::actingAs($user);

        $response = $this->deleteJson('/api/v1/admin/users/' . $otherUser->id);

        $response->assertStatus(403);
    }

    public function test_admin_can_see_stats(): void {

        $admin = User::factory()->create([
            'role' => 'admin'
        ]);

        User::factory()->count(2)->create();

        Passport::actingAs($admin);

        $response = $this->getJson('/api/v1/admin/stats');

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'total_users',
                    'total_resources',
                    'total_folders',
                    'total_tags',
                    'tags'
                ]);
    }

    public function test_non_admin_cannot_see_stats(): void {

        $user = User::factory()->create();

        Passport::actingAs($user);

        $response = $this->getJson('/api/v1/admin/stats');

        $response->assertStatus(403);
    }
}
