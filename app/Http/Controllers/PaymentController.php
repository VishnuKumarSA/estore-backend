<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Razorpay\Api\Api;

class PaymentController extends Controller
{
    public function createOrder(Request $request)
    {
        try {

            $api = new Api(
                config('services.razorpay.key'),
                config('services.razorpay.secret')
            );

            $order = $api->order->create([
                'receipt' => 'receipt_' . time(),
                'amount' => (int) $request->amount * 100,
                'currency' => 'INR',
            ]);

            return response()->json([
                'key' => config('services.razorpay.key'),
                'request_amount' => $request->amount,
                'razorpay_amount' => $order['amount'],
                'order' => [
                    'id' => $order['id'],
                    'amount' => $order['amount'],
                    'currency' => $order['currency'],
                ],
            ]);

        } catch (\Exception $e) {

            return response()->json([
                'error' => $e->getMessage(),
            ], 500);

        }
    }

    public function verify(Request $request)
    {
        $api = new Api(
            config('services.razorpay.key'),
            config('services.razorpay.secret')
        );

        try {

            $attributes = [
                'razorpay_order_id' => $request->razorpay_order_id,
                'razorpay_payment_id' => $request->razorpay_payment_id,
                'razorpay_signature' => $request->razorpay_signature,
            ];

            // Verify Signature
            $api->utility->verifyPaymentSignature($attributes);

            // Fetch Payment
            $payment = $api->payment->fetch($request->razorpay_payment_id);

            if ($payment->order_id !== $request->razorpay_order_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid Razorpay Order ID'
                ], 400);
            }

            // Check Payment Status
            if ($payment->status !== 'captured') {
                return response()->json([
                    'success' => false,
                    'message' => 'Payment not captured'
                ], 400);
            }

            return response()->json([
                'success' => true
            ]);

        } catch (\Exception $e) {

            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }
}