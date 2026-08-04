<?php

namespace App\Http\Controllers;

use App\Models\Plan;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class PaymentController extends Controller
{
    public function index()
    {
        $plans = Plan::where('is_active', true)->get();
        return view('pricing', compact('plans'));
    }

    public function checkout(Request $request, Plan $plan)
    {
        $request->validate([
            'phone' => 'required|digits:10',
        ]);

        $user = auth()->user();

        // 1. Create a local Payment record with 'pending' status
        $payment = Payment::create([
            'user_id' => $user->id,
            'plan_id' => $plan->id,
            'amount' => $plan->price,
            'status' => 'pending',
        ]);

        // 2. Prepare Instamojo payload
        $payload = [
            'purpose' => $plan->name,
            'amount' => $plan->price,
            'buyer_name' => $user->name,
            'email' => $user->email,
            'phone' => $request->phone,
            'redirect_url' => route('payment.callback'),
            'send_email' => true,
            'send_sms' => true,
            'webhook' => '', // Optional if we rely on redirect
            'allow_repeated_payments' => false,
        ];

        Log::info('Instamojo Payload:', $payload);

        // 3. Call Instamojo API
        $response = Http::withHeaders([
            'X-Api-Key' => config('services.instamojo.api_key'),
            'X-Auth-Token' => config('services.instamojo.auth_token'),
        ])->post('https://www.instamojo.com/api/1.1/payment-requests/', $payload);

        // Note: Using v1.1 endpoint structure which is common. If v2 is preferred: https://api.instamojo.com/v2/payment_requests/
        // Let's stick to the standard v1.1 for simplicity unless it fails, or check doc.
        // Actually, let's use the API wrapper or just direct HTTP. Direct HTTP is fine.
        
        $json = $response->json();

        if ($response->successful() && isset($json['payment_request'])) {
            $longUrl = $json['payment_request']['longurl'];
            $paymentRequestId = $json['payment_request']['id'];

            // Update payment record
            $payment->update(['payment_request_id' => $paymentRequestId]);

            // Redirect user
            return redirect($longUrl);
        } else {
            Log::error('Instamojo Payment Initiation Failed', ['response' => $json]);
            return back()->with('error', 'Unable to initiate payment. Please try again later.');
        }
    }

    public function callback(Request $request)
    {
        // Instamojo redirects back with ?payment_id=MOJOxxx&payment_request_id=xxx
        $paymentId = $request->payment_id;
        $paymentRequestId = $request->payment_request_id;
        $status = $request->status; // Sometimes 'Credit' or something similar? No, usually check payment details.

        // Find our local payment
        $payment = Payment::where('payment_request_id', $paymentRequestId)->firstOrFail();

        // Verify with Instamojo API to be sure
        $response = Http::withHeaders([
            'X-Api-Key' => config('services.instamojo.api_key'),
            'X-Auth-Token' => config('services.instamojo.auth_token'),
        ])->get("https://www.instamojo.com/api/1.1/payment-requests/{$paymentRequestId}/{ $paymentId }/");
        
        // Actually, a simpler way is checking the payment details by ID
        $response = Http::withHeaders([
            'X-Api-Key' => config('services.instamojo.api_key'),
            'X-Auth-Token' => config('services.instamojo.auth_token'),
        ])->get("https://www.instamojo.com/api/1.1/payments/{$paymentId}/");

        $json = $response->json();

        if ($response->successful() && $json['payment']['status'] == 'Credit') {
            // Payment Successful
            $payment->update([
                'payment_id' => $paymentId,
                'status' => 'completed', 
            ]);

            // Update User Subscription
            $user = $payment->user;
            $plan = $payment->plan;

            // Logic: If user already has valid subscription, extend it. If not, start from now.
            $currentExpiry = $user->subscription_expires_at;
            
            if ($currentExpiry && $currentExpiry->isFuture()) {
                $newExpiry = $currentExpiry->addDays($plan->duration_days);
            } else {
                $newExpiry = Carbon::now()->addDays($plan->duration_days);
            }

            $user->plan_id = $plan->id;
            $user->subscription_expires_at = $newExpiry;
            $user->save();

            return redirect()->route('filament.student.pages.dashboard')->with('success', 'Payment successful! Subscription active until ' . $newExpiry->toFormattedDateString());

        } else {
            // Payment Failed or Pending
            $payment->update(['status' => 'failed']);
            return redirect()->route('pricing')->with('error', 'Payment failed or was incomplete.');
        }
    }
}
