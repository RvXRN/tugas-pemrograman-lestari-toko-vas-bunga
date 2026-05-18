<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminDashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_cannot_access_dashboard()
    {
        $customer = User::factory()->create(['role' => 'customer']);
        $response = $this->actingAs($customer)->getJson('/api/v1/admin/dashboard/stats');
        
        $response->assertStatus(403);
    }

    public function test_admin_can_view_dashboard_stats()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $customer1 = User::factory()->create(['role' => 'customer']);
        $customer2 = User::factory()->create(['role' => 'customer']);

        $category = Category::create(['name' => 'Bunga', 'slug' => 'bunga']);
        
        $productLowStock = Product::create([
            'category_id' => $category->id,
            'name' => 'Melati',
            'slug' => 'melati',
            'description' => 'Bunga melati',
            'price' => 20000,
            'stock' => 2,
            'weight' => 100,
            'is_active' => true,
        ]);

        $productNormalStock = Product::create([
            'category_id' => $category->id,
            'name' => 'Mawar',
            'slug' => 'mawar',
            'description' => 'Bunga mawar',
            'price' => 25000,
            'stock' => 50,
            'weight' => 200,
            'is_active' => true,
        ]);

        Order::create([
            'user_id' => $customer1->id,
            'order_number' => 'INV-111',
            'total_amount' => 100000,
            'status' => 'paid',
        ]);

        Order::create([
            'user_id' => $customer2->id,
            'order_number' => 'INV-222',
            'total_amount' => 50000,
            'status' => 'paid',
        ]);

        Order::create([
            'user_id' => $customer1->id,
            'order_number' => 'INV-333',
            'total_amount' => 75000,
            'status' => 'pending', // should not be counted in revenue
        ]);

        $response = $this->actingAs($admin)->getJson('/api/v1/admin/dashboard/stats');

        $response->assertStatus(200)
            ->assertJsonPath('data.current_month.revenue', 150000)
            ->assertJsonPath('data.current_month.orders_count', 2)
            ->assertJsonPath('data.current_month.new_customers', 2)
            ->assertJsonCount(1, 'data.alerts.low_stock_products')
            ->assertJsonPath('data.alerts.low_stock_products.0.name', 'Melati');
    }
}
