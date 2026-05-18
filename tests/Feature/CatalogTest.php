<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CatalogTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_can_view_active_products()
    {
        $category = Category::create(['name' => 'Mawar', 'slug' => 'mawar']);
        
        Product::create([
            'category_id' => $category->id,
            'name' => 'Mawar Putih',
            'slug' => 'mawar-putih',
            'description' => 'Putih',
            'price' => 10000,
            'stock' => 5,
            'weight' => 100,
            'is_active' => true,
        ]);
        
        Product::create([
            'category_id' => $category->id,
            'name' => 'Mawar Layu',
            'slug' => 'mawar-layu',
            'description' => 'Layu',
            'price' => 5000,
            'stock' => 0,
            'weight' => 100,
            'is_active' => false,
        ]);

        $response = $this->getJson('/api/v1/catalog/products');

        $response->assertStatus(200);
        $this->assertCount(1, $response->json('data.data'));
        $this->assertEquals('Mawar Putih', $response->json('data.data.0.name'));
    }
    
    public function test_public_can_search_products_by_name()
    {
        $category = Category::create(['name' => 'Mawar', 'slug' => 'mawar']);
        
        Product::create([
            'category_id' => $category->id,
            'name' => 'Anggrek Merah',
            'slug' => 'anggrek-merah',
            'description' => 'Merah',
            'price' => 10000,
            'stock' => 5,
            'weight' => 100,
            'is_active' => true,
        ]);

        $response = $this->getJson('/api/v1/catalog/products?search=anggrek');
        $response->assertStatus(200);
        $this->assertCount(1, $response->json('data.data'));
        $this->assertEquals('Anggrek Merah', $response->json('data.data.0.name'));
    }
}
