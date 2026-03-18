<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use App\Models\Folder;
use App\Models\Resource;
use App\Models\Tag;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Passport\Passport;
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
}
