<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdminProductTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->admin = User::factory()->create(['role' => 'admin']);
        $this->category = Category::create(['name' => 'Mawar', 'slug' => 'mawar']);
    }

    public function test_admin_can_create_product_with_image()
    {
        Storage::fake('public');

        $file = UploadedFile::fake()->image('flower.jpg');

        $response = $this->actingAs($this->admin)->postJson('/api/v1/admin/products', [
            'category_id' => $this->category->id,
            'name' => 'Mawar Merah',
            'description' => 'Mawar merah segar',
            'price' => 50000,
            'stock' => 10,
            'weight' => 500,
            'is_active' => true,
            'images' => [$file],
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('products', ['name' => 'Mawar Merah']);
        
        $product = Product::first();
        $this->assertCount(1, $product->images);
        Storage::disk('public')->assertExists($product->images->first()->image_path);
    }

    public function test_product_price_cannot_be_negative()
    {
        $response = $this->actingAs($this->admin)->postJson('/api/v1/admin/products', [
            'category_id' => $this->category->id,
            'name' => 'Mawar Hitam',
            'description' => 'Mawar',
            'price' => -1000,
            'stock' => 5,
            'weight' => 200,
        ]);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['price']);
    }
}
