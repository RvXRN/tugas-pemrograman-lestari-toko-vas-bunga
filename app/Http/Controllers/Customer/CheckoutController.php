<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Jobs\ProcessCheckoutJob;
use App\Models\Cart;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CheckoutController extends Controller
{
    use ApiResponseTrait;

    public function process(Request $request): JsonResponse
    {
        $user = $request->user();
        
        $cart = Cart::where('user_id', $user->id)->first();
        
        if (!$cart || $cart->items()->count() === 0) {
            return $this->errorResponse('Keranjang Anda kosong.', 400);
        }

        ProcessCheckoutJob::dispatch($user->id, $cart->id);

        return $this->successResponse(
            null, 
            'Proses checkout sedang berjalan di latar belakang. Silakan tunggu notifikasi.', 
            202
        );
    }
}
