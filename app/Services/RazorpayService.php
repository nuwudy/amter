<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class RazorpayService
{
    protected ?string $keyId;
    protected ?string $keySecret;
    protected ?string $webhookSecret;
    protected string $baseUrl = 'https://api.razorpay.com/v1';

    public function __construct()
    {
        $this->keyId = config('services.razorpay.key_id');
        $this->keySecret = config('services.razorpay.key_secret');
        $this->webhookSecret = config('services.razorpay.webhook_secret');
    }

    public function isConfigured(): bool
    {
        return !empty($this->keyId) && !empty($this->keySecret);
    }

    public function getKeyId(): string
    {
        if (empty($this->keyId)) {
            throw new RuntimeException('Razorpay Key ID is not configured. Please set RAZORPAY_KEY_ID in your .env file.');
        }

        return $this->keyId;
    }

    public function getKeySecret(): string
    {
        if (empty($this->keySecret)) {
            throw new RuntimeException('Razorpay Key Secret is not configured. Please set RAZORPAY_KEY_SECRET in your .env file.');
        }

        return $this->keySecret;
    }

    /**
     * Create a Razorpay Order.
     *
     * @param float|int $amountInRupees
     * @param string $receipt
     * @param array $notes
     * @return array
     */
    public function createOrder(float|int $amountInRupees, string $receipt, array $notes = []): array
    {
        $keyId = $this->getKeyId();
        $keySecret = $this->getKeySecret();

        // Amount in smallest unit (paise for INR)
        $amountInPaise = (int) round($amountInRupees * 100);

        $payload = [
            'amount' => $amountInPaise,
            'currency' => 'INR',
            'receipt' => $receipt,
            'notes' => $notes,
        ];

        Log::info('Initiating Razorpay Order', [
            'receipt' => $receipt,
            'amount' => $amountInPaise,
        ]);

        $response = Http::withBasicAuth($keyId, $keySecret)
            ->timeout(15)
            ->post("{$this->baseUrl}/orders", $payload);

        if (!$response->successful()) {
            $error = $response->json();
            Log::error('Razorpay Order Creation Failed', [
                'status' => $response->status(),
                'response' => $error,
            ]);

            $message = $error['error']['description'] ?? 'Failed to create Razorpay order.';
            throw new RuntimeException($message);
        }

        return $response->json();
    }

    /**
     * Verify the payment signature returned by Razorpay checkout modal.
     *
     * @param string $orderId
     * @param string $paymentId
     * @param string $signature
     * @return bool
     */
    public function verifyPaymentSignature(string $orderId, string $paymentId, string $signature): bool
    {
        $keySecret = $this->getKeySecret();
        $expectedSignature = hash_hmac('sha256', $orderId . '|' . $paymentId, $keySecret);

        return hash_equals($expectedSignature, $signature);
    }

    /**
     * Verify the webhook signature from Razorpay.
     *
     * @param string $payload
     * @param string $signature
     * @return bool
     */
    public function verifyWebhookSignature(string $payload, string $signature): bool
    {
        if (empty($this->webhookSecret)) {
            Log::warning('Razorpay Webhook Secret not configured. Cannot verify webhook signature.');
            return false;
        }

        $expectedSignature = hash_hmac('sha256', $payload, $this->webhookSecret);

        return hash_equals($expectedSignature, $signature);
    }

    /**
     * Fetch payment details from Razorpay API.
     *
     * @param string $paymentId
     * @return array|null
     */
    public function fetchPayment(string $paymentId): ?array
    {
        try {
            $keyId = $this->getKeyId();
            $keySecret = $this->getKeySecret();

            $response = Http::withBasicAuth($keyId, $keySecret)
                ->timeout(15)
                ->get("{$this->baseUrl}/payments/{$paymentId}");

            if ($response->successful()) {
                return $response->json();
            }

            Log::warning('Failed to fetch Razorpay payment details', [
                'payment_id' => $paymentId,
                'status' => $response->status(),
                'response' => $response->json(),
            ]);
        } catch (\Throwable $e) {
            Log::error('Exception while fetching Razorpay payment: ' . $e->getMessage());
        }

        return null;
    }
}
