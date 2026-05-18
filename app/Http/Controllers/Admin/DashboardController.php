<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Traits\ApiResponseTrait;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;

class DashboardController extends Controller
{
    use ApiResponseTrait;

    public function stats(): JsonResponse
    {
        $startOfMonth = Carbon::now()->startOfMonth();
        $endOfMonth = Carbon::now()->endOfMonth();

        $totalRevenue = Order::where('status', 'paid')
            ->whereBetween('created_at', [$startOfMonth, $endOfMonth])
            ->sum('total_amount');

        $totalOrders = Order::where('status', 'paid')
            ->whereBetween('created_at', [$startOfMonth, $endOfMonth])
            ->count();

        $newCustomers = User::where('role', 'customer')
            ->whereBetween('created_at', [$startOfMonth, $endOfMonth])
            ->count();

        $lowStockProducts = Product::where('stock', '<', 5)
            ->where('is_active', true)
            ->select('id', 'name', 'stock')
            ->orderBy('stock', 'asc')
            ->take(5)
            ->get();

        return $this->successResponse([
            'current_month' => [
                'revenue' => (int) $totalRevenue,
                'orders_count' => $totalOrders,
                'new_customers' => $newCustomers,
            ],
            'alerts' => [
                'low_stock_products' => $lowStockProducts
            ]
        ], 'Dashboard stats retrieved successfully');
    }
}
