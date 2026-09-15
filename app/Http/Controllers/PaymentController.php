<?php

namespace App\Http\Controllers;

use App\Models\Plan;
use App\Models\Payment;
use App\Services\RazorpayService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class PaymentController extends Controller
{
    /**
     * Display the pricing page.
     */
    public function index()
    {
        $plans = Plan::where('is_active', true)->get();
        $razorpayKey = config('services.razorpay.key_id');

        return view('pricing', compact('plans', 'razorpayKey'));
    }

    /**
     * Initiate a Razorpay payment order.
     */
    public function initiate(Request $request, Plan $plan, RazorpayService $razorpay): JsonResponse
    {
        $request->validate([
            'phone' => 'nullable|string|max:20',
        ]);

        $user = auth()->user();

        if (!$razorpay->isConfigured()) {
            return response()->json([
                'success' => false,
                'message' => 'Online card & UPI checkout is currently being activated. Please contact our support team via WhatsApp / Call (+91 98959 40500) or email (amterglobal@gmail.com) to activate your plan directly.',
            ], 200);
        }

        try {
            $receipt = 'rcpt_' . $plan->id . '_' . $user->id . '_' . time();

            // 1. Create Order with Razorpay
            $order = $razorpay->createOrder(
                amountInRupees: $plan->price,
                receipt: $receipt,
                notes: [
                    'user_id' => (string) $user->id,
                    'user_email' => $user->email,
                    'plan_id' => (string) $plan->id,
                    'plan_name' => $plan->name,
                ]
            );

            // 2. Create pending payment record
            Payment::create([
                'user_id' => $user->id,
                'plan_id' => $plan->id,
                'order_id' => $order['id'],
                'receipt' => $receipt,
                'amount' => $plan->price,
                'currency' => 'INR',
                'phone' => $request->input('phone'),
                'status' => 'pending',
            ]);

            return response()->json([
                'success' => true,
                'key' => $razorpay->getKeyId(),
                'order_id' => $order['id'],
                'amount' => $order['amount'],
                'currency' => $order['currency'] ?? 'INR',
                'name' => config('app.name', 'Amter English'),
                'description' => $plan->name . ' (' . $plan->duration_days . ' Days)',
                'prefill' => [
                    'name' => $user->name,
                    'email' => $user->email,
                    'contact' => $request->input('phone') ?? '',
                ],
                'theme' => [
                    'color' => '#6366f1',
                ],
            ]);
        } catch (Throwable $e) {
            Log::error('Razorpay Initiate Error: ' . $e->getMessage(), [
                'user_id' => $user->id,
                'plan_id' => $plan->id,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Unable to initiate payment: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Verify payment signature and activate user subscription.
     */
    public function verify(Request $request, RazorpayService $razorpay): JsonResponse
    {
        $request->validate([
            'razorpay_order_id' => 'required|string',
            'razorpay_payment_id' => 'required|string',
            'razorpay_signature' => 'required|string',
        ]);

        $orderId = $request->input('razorpay_order_id');
        $paymentId = $request->input('razorpay_payment_id');
        $signature = $request->input('razorpay_signature');

        $payment = Payment::where('order_id', $orderId)->first();

        if (!$payment) {
            Log::error('Payment record not found for Order ID: ' . $orderId);
            return response()->json([
                'success' => false,
                'message' => 'Payment transaction record not found.',
            ], 404);
        }

        // Verify cryptographic signature
        $isSignatureValid = $razorpay->verifyPaymentSignature($orderId, $paymentId, $signature);

        if (!$isSignatureValid) {
            $payment->update([
                'payment_id' => $paymentId,
                'signature' => $signature,
                'status' => 'failed',
                'error_reason' => 'Cryptographic signature verification failed.',
            ]);

            Log::warning('Razorpay signature mismatch for Order: ' . $orderId);

            return response()->json([
                'success' => false,
                'message' => 'Payment verification failed: Invalid payment signature.',
            ], 400);
        }

        // Fetch payment details to record method (UPI, card, etc.)
        $details = $razorpay->fetchPayment($paymentId);
        $method = $details['method'] ?? null;

        // Fulfill subscription inside transaction
        DB::transaction(function () use ($payment, $paymentId, $signature, $method) {
            $payment->update([
                'payment_id' => $paymentId,
                'signature' => $signature,
                'method' => $method,
                'status' => 'completed',
            ]);

            $this->fulfillSubscription($payment);
        });

        session()->flash('success', 'Payment successful! Your subscription is now active.');

        return response()->json([
            'success' => true,
            'message' => 'Payment successful!',
            'redirect_url' => route('filament.student.pages.dashboard'),
        ]);
    }

    /**
     * Handle Razorpay Webhook events asynchronously.
     */
    public function webhook(Request $request, RazorpayService $razorpay)
    {
        $payload = $request->getContent();
        $signature = $request->header('X-Razorpay-Signature');

        if (!$signature || !$razorpay->verifyWebhookSignature($payload, $signature)) {
            Log::warning('Razorpay Webhook: Invalid signature');
            return response()->json(['status' => 'invalid_signature'], 400);
        }

        $event = json_decode($payload, true);
        $eventName = $event['event'] ?? '';

        Log::info("Razorpay Webhook received: {$eventName}", ['event_id' => $event['id'] ?? null]);

        if (in_array($eventName, ['payment.captured', 'order.paid'])) {
            $paymentEntity = $event['payload']['payment']['entity'] ?? null;
            $orderId = $paymentEntity['order_id'] ?? null;
            $paymentId = $paymentEntity['id'] ?? null;

            if ($orderId) {
                $payment = Payment::where('order_id', $orderId)->first();

                if ($payment && $payment->status !== 'completed') {
                    DB::transaction(function () use ($payment, $paymentEntity, $paymentId) {
                        $payment->update([
                            'payment_id' => $paymentId,
                            'method' => $paymentEntity['method'] ?? $payment->method,
                            'status' => 'completed',
                        ]);

                        $this->fulfillSubscription($payment);
                    });

                    Log::info("Subscription fulfilled via Razorpay Webhook for Order: {$orderId}");
                }
            }
        } elseif ($eventName === 'payment.failed') {
            $paymentEntity = $event['payload']['payment']['entity'] ?? null;
            $orderId = $paymentEntity['order_id'] ?? null;

            if ($orderId) {
                $payment = Payment::where('order_id', $orderId)->first();
                if ($payment && $payment->status === 'pending') {
                    $payment->update([
                        'status' => 'failed',
                        'error_reason' => $paymentEntity['error_description'] ?? 'Payment failed via webhook notification',
                    ]);
                }
            }
        }

        return response()->json(['status' => 'success']);
    }

    /**
     * Extend or activate user subscription based on purchased plan.
     */
    protected function fulfillSubscription(Payment $payment): void
    {
        $user = $payment->user;
        $plan = $payment->plan;

        if (!$user || !$plan) {
            return;
        }

        $currentExpiry = $user->subscription_expires_at;

        if ($currentExpiry && $currentExpiry->isFuture()) {
            $newExpiry = $currentExpiry->copy()->addDays($plan->duration_days);
        } else {
            $newExpiry = Carbon::now()->addDays($plan->duration_days);
        }

        $user->plan_id = $plan->id;
        $user->subscription_expires_at = $newExpiry;
        $user->save();
    }
}
