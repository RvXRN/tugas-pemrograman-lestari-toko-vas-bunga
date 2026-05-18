<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Payment;
use App\Services\MidtransService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    protected MidtransService $midtransService;

    public function __construct(MidtransService $midtransService)
    {
        $this->midtransService = $midtransService;
    }

    public function webhook(Request $request): JsonResponse
    {
        $payload = $request->all();

        $orderId = $payload['order_id'] ?? '';
        $statusCode = $payload['status_code'] ?? '';
        $grossAmount = $payload['gross_amount'] ?? '';
        $signatureKey = $payload['signature_key'] ?? '';

        $isValid = $this->midtransService->verifySignatureKey($orderId, $statusCode, $grossAmount, $signatureKey);

        if (!$isValid) {
            return response()->json(['message' => 'Invalid signature'], 403);
        }

        $order = Order::where('order_number', $orderId)->first();

        if (!$order) {
            return response()->json(['message' => 'Order not found'], 404);
        }

        $transactionStatus = $payload['transaction_status'] ?? '';
        $fraudStatus = $payload['fraud_status'] ?? null;

        $paymentStatus = 'pending';
        $orderStatus = $order->status;

        if ($transactionStatus == 'capture') {
            if ($fraudStatus == 'challenge') {
                $paymentStatus = 'challenge';
                $orderStatus = 'pending';
            } else if ($fraudStatus == 'accept') {
                $paymentStatus = 'success';
                $orderStatus = 'paid';
            }
        } else if ($transactionStatus == 'settlement') {
            $paymentStatus = 'success';
            $orderStatus = 'paid';
        } else if ($transactionStatus == 'cancel' ||
            $transactionStatus == 'deny' ||
            $transactionStatus == 'expire') {
            $paymentStatus = 'failed';
            $orderStatus = 'cancelled';
        } else if ($transactionStatus == 'pending') {
            $paymentStatus = 'pending';
            $orderStatus = 'pending';
        }

        $order->update(['status' => $orderStatus]);

        Payment::updateOrCreate(
            ['order_id' => $order->id],
            [
                'transaction_id' => $payload['transaction_id'] ?? '',
                'payment_type' => $payload['payment_type'] ?? '',
                'gross_amount' => $grossAmount,
                'status' => $paymentStatus,
                'payload' => $payload,
            ]
        );

        return response()->json(['message' => 'Webhook handled successfully'], 200);
    }
}
