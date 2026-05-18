<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminCategoryTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->admin = User::factory()->create(['role' => 'admin']);
        $this->customer = User::factory()->create(['role' => 'customer']);
    }

    public function test_customer_cannot_access_categories_admin()
    {
        $response = $this->actingAs($this->customer)->getJson('/api/v1/admin/categories');
        $response->assertStatus(403);
    }

    public function test_admin_can_create_category()
    {
        $response = $this->actingAs($this->admin)->postJson('/api/v1/admin/categories', [
            'name' => 'Bunga Papan',
        ]);

        $response->assertStatus(201)
                 ->assertJsonPath('data.slug', 'bunga-papan');
                 
        $this->assertDatabaseHas('categories', ['name' => 'Bunga Papan']);
    }

    public function test_admin_can_update_category()
    {
        $category = Category::create(['name' => 'Old Name', 'slug' => 'old-name']);

        $response = $this->actingAs($this->admin)->putJson("/api/v1/admin/categories/{$category->id}", [
            'name' => 'New Name',
        ]);

        $response->assertStatus(200)
                 ->assertJsonPath('data.slug', 'new-name');
    }
}
