<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\Order;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    use ApiResponseTrait;

    public function stats(Request $request): JsonResponse
    {
        $user = $request->user();

        // Total Belanja (completed or paid)
        $totalSpent = Order::where('user_id', $user->id)
            ->whereIn('status', ['paid', 'completed'])
            ->sum('total_amount');

        // Total Pesanan Keseluruhan
        $totalOrders = Order::where('user_id', $user->id)->count();

        // 5 Pesanan Terbaru
        $recentOrders = Order::where('user_id', $user->id)
            ->with(['items.product' => function ($query) {
                $query->select('id', 'name', 'slug');
            }])
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        // Jumlah Item di Keranjang
        $cart = Cart::where('user_id', $user->id)->first();
        $cartItemsCount = $cart ? $cart->items()->sum('quantity') : 0;

        return $this->successResponse([
            'summary' => [
                'total_spent' => (int) $totalSpent,
                'total_orders' => $totalOrders,
                'active_cart_items' => (int) $cartItemsCount,
            ],
            'recent_orders' => $recentOrders
        ], 'Customer dashboard stats retrieved successfully');
    }
}
