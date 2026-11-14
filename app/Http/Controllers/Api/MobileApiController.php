<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\CourseSession;
use App\Models\LearningTrack;
use App\Models\MediaItem;
use App\Models\Payment;
use App\Models\Plan;
use App\Models\Unit;
use App\Models\User;
use App\Services\MilestoneService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class MobileApiController extends Controller
{
    /**
     * POST /api/login
     * Authenticate a student or admin user and issue a Sanctum Bearer token.
     */
    public function login(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
            'device_name' => ['nullable', 'string', 'max:120'],
        ]);

        $email = strtolower(trim($validated['email']));
        $user = User::with('plan')->where('email', $email)->first();

        if (!$user || !Hash::check($validated['password'], $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'The provided credentials do not match our records.',
                'errors' => [
                    'email' => ['The provided credentials do not match our records.'],
                ],
            ], 401);
        }

        if ($user->is_suspended) {
            return response()->json([
                'success' => false,
                'message' => 'Your account has been suspended. Please contact support at amterglobal@gmail.com.',
            ], 403);
        }

        $deviceName = trim((string) ($validated['device_name'] ?? $request->userAgent() ?? 'amter-mobile-app')) ?: 'amter-mobile-app';
        $token = $user->createToken(mb_substr($deviceName, 0, 120))->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Login successful.',
            'token' => $token,
            'token_type' => 'Bearer',
            'user' => $this->formatUser($user),
        ]);
    }

    /**
     * POST /api/register
     * Register a new student account and issue a Sanctum Bearer token.
     */
    public function register(Request $request): JsonResponse
    {
        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:6'],
            'device_name' => ['nullable', 'string', 'max:120'],
        ];

        if ($request->filled('password_confirmation')) {
            $rules['password'][] = 'confirmed';
        }

        $validated = $request->validate($rules);

        $user = User::create([
            'name' => trim($validated['name']),
            'email' => strtolower(trim($validated['email'])),
            'password' => $validated['password'],
            'role' => 'student',
        ]);

        $user->load('plan');

        $deviceName = trim((string) ($validated['device_name'] ?? $request->userAgent() ?? 'amter-mobile-app')) ?: 'amter-mobile-app';
        $token = $user->createToken(mb_substr($deviceName, 0, 120))->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Registration successful.',
            'token' => $token,
            'token_type' => 'Bearer',
            'user' => $this->formatUser($user),
        ], 201);
    }

    /**
     * POST /api/logout
     * Revoke the current Sanctum access token.
     */
    public function logout(Request $request): JsonResponse
    {
        $user = $request->user();
        if ($user && $user->currentAccessToken()) {
            $user->currentAccessToken()->delete();
        }

        return response()->json([
            'success' => true,
            'message' => 'Logged out successfully.',
        ]);
    }

    /**
     * GET /api/user & GET /api/profile
     * Return authenticated user profile, subscription status, and learning progress.
     */
    public function profile(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user()->load('plan');

        $recentPayments = Payment::with('plan')
            ->where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get()
            ->map(fn (Payment $p) => [
                'id' => $p->id,
                'order_id' => $p->order_id,
                'payment_id' => $p->payment_id,
                'amount' => (float) $p->amount,
                'currency' => $p->currency ?? 'INR',
                'method' => $p->method,
                'status' => $p->status,
                'plan_name' => $p->plan?->name,
                'created_at' => $p->created_at?->toIso8601String(),
            ]);

        return response()->json([
            'success' => true,
            'user' => $this->formatUser($user, true),
            'recent_payments' => $recentPayments,
        ]);
    }

    /**
     * PUT/PATCH /api/profile
     * Update authenticated user's profile details or password.
     */
    public function updateProfile(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        $validated = $request->validate([
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'email' => ['sometimes', 'required', 'string', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'current_password' => ['nullable', 'required_with:password', 'string'],
            'password' => ['nullable', 'string', 'min:6'],
        ]);

        if (!empty($validated['password'])) {
            if (!Hash::check((string) ($validated['current_password'] ?? ''), $user->password)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Current password is incorrect.',
                    'errors' => [
                        'current_password' => ['The provided current password is incorrect.'],
                    ],
                ], 422);
            }
            $user->password = $validated['password'];
        }

        if (isset($validated['name'])) {
            $user->name = trim($validated['name']);
        }

        if (isset($validated['email'])) {
            $user->email = strtolower(trim($validated['email']));
        }

        $user->save();
        $user->load('plan');

        return response()->json([
            'success' => true,
            'message' => 'Profile updated successfully.',
            'user' => $this->formatUser($user, true),
        ]);
    }

    /**
     * DELETE /api/user & POST /api/delete-account
     * Permanently delete the authenticated user's account and associated data (Google Play requirement).
     */
    public function deleteAccount(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        if ($request->filled('password') && !Hash::check((string) $request->input('password'), $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid password confirmation.',
                'errors' => [
                    'password' => ['The password you entered is incorrect.'],
                ],
            ], 422);
        }

        DB::transaction(function () use ($user) {
            $user->tokens()->delete();
            $user->completedUnits()->detach();
            $user->courses()->detach();
            $user->milestones()->delete();
            $user->delete();
        });

        return response()->json([
            'success' => true,
            'message' => 'Your account and associated personal data have been permanently deleted.',
        ]);
    }

    /**
     * GET /api/products & GET /api/plans
     * List all active digital subscription plans/products.
     */
    public function products(): JsonResponse
    {
        $plans = Plan::where('is_active', true)
            ->orderBy('duration_days', 'asc')
            ->get()
            ->map(fn (Plan $plan) => $this->formatPlan($plan))
            ->values();

        return response()->json([
            'success' => true,
            'products' => $plans,
        ]);
    }

    /**
     * GET /api/content
     * Return digital products, courses, learning tracks, and course sessions (library catalog).
     */
    public function content(Request $request): JsonResponse
    {
        /** @var User|null $user */
        $user = $request->user('sanctum');
        $completedUnitIds = $user
            ? $user->completedUnits()->pluck('units.id')->flip()->toArray()
            : [];

        $plans = Plan::where('is_active', true)
            ->orderBy('duration_days', 'asc')
            ->get()
            ->map(fn (Plan $plan) => $this->formatPlan($plan))
            ->values();

        $courses = Course::where('is_published', true)
            ->with(['modules' => function ($q) {
                $q->orderBy('sort_order', 'asc');
            }])
            ->withCount('units')
            ->get()
            ->map(fn (Course $course) => [
                'id' => $course->id,
                'title' => $course->title,
                'slug' => $course->slug ?? Str::slug($course->title),
                'description' => $course->description,
                'thumbnail_url' => $this->resolveAssetUrl($course->thumbnail ?? $course->image_path ?? null),
                'units_count' => (int) $course->units_count,
                'modules' => $course->modules->map(fn ($m) => [
                    'id' => $m->id,
                    'name' => $m->name,
                    'slug' => $m->slug,
                    'description' => $m->description,
                    'thumbnail_url' => $this->resolveAssetUrl($m->thumbnail ?? $m->image_path ?? null),
                    'sort_order' => (int) $m->sort_order,
                ])->values(),
            ])
            ->values();

        $sessions = CourseSession::with(['module', 'units' => function ($q) {
            $q->where('is_published', true)->orderBy('sort_order', 'asc');
        }])
            ->whereHas('units', fn ($q) => $q->where('is_published', true))
            ->orderBy('sort_order', 'asc')
            ->orderBy('id', 'asc')
            ->get()
            ->map(fn (CourseSession $session) => $this->formatSessionSummary($session, $completedUnitIds))
            ->values();

        $tracks = LearningTrack::where('is_active', true)
            ->withCount('trackUnits')
            ->orderBy('id', 'asc')
            ->get()
            ->map(fn (LearningTrack $track) => [
                'id' => $track->id,
                'title' => $track->title,
                'slug' => $track->slug,
                'description' => $track->description,
                'is_primary' => (bool) ($track->is_primary ?? false),
                'steps_count' => (int) $track->track_units_count,
            ])
            ->values();

        return $this->noCacheJson([
            'success' => true,
            'products' => $plans,
            'courses' => $courses,
            'sessions' => $sessions,
            'tracks' => $tracks,
        ]);
    }

    /**
     * GET /api/sessions
     * List all published course sessions (Library cards) with user progress.
     */
    public function sessions(Request $request): JsonResponse
    {
        /** @var User|null $user */
        $user = $request->user('sanctum');
        $completedUnitIds = $user
            ? $user->completedUnits()->pluck('units.id')->flip()->toArray()
            : [];

        $sessions = CourseSession::with(['module', 'units' => function ($q) {
            $q->where('is_published', true)->orderBy('sort_order', 'asc');
        }])
            ->when($request->filled('module_id'), fn ($q) => $q->where('module_id', (int) $request->query('module_id')))
            ->whereHas('units', fn ($q) => $q->where('is_published', true))
            ->orderBy('sort_order', 'asc')
            ->orderBy('id', 'asc')
            ->get()
            ->map(fn (CourseSession $session) => $this->formatSessionSummary($session, $completedUnitIds))
            ->values();

        return $this->noCacheJson([
            'success' => true,
            'sessions' => $sessions,
        ]);
    }

    /**
     * GET /api/sessions/{session}
     * Return a single CourseSession and its ordered list of published lessons (units).
     */
    public function sessionDetail(Request $request, CourseSession $session): JsonResponse
    {
        /** @var User|null $user */
        $user = $request->user('sanctum');
        $completedUnitIds = $user
            ? $user->completedUnits()->pluck('units.id')->flip()->toArray()
            : [];

        $session->load(['module', 'units' => function ($q) {
            $q->where('is_published', true)->orderBy('sort_order', 'asc');
        }]);

        $units = $session->units->map(function (Unit $unit) use ($user, $completedUnitIds) {
            $rawTitle = trim((string) $unit->title);
            $displayTitle = ctype_digit($rawTitle) ? "Lesson {$rawTitle}" : $rawTitle;

            return [
                'id' => $unit->id,
                'course_session_id' => $unit->course_session_id,
                'sort_order' => (int) $unit->sort_order,
                'title' => $unit->title,
                'display_title' => $displayTitle,
                'type' => $unit->type,
                'thumbnail_url' => $this->resolveAssetUrl($unit->thumbnail),
                'is_free_sample' => (bool) $unit->is_free_sample,
                'is_registered_only' => (bool) $unit->is_registered_only,
                'is_accessible' => $unit->isAccessibleBy($user),
                'is_completed' => isset($completedUnitIds[$unit->id]),
            ];
        })->values();

        return $this->noCacheJson([
            'success' => true,
            'session' => $this->formatSessionSummary($session, $completedUnitIds),
            'units' => $units,
        ]);
    }

    /**
     * GET /api/units/{unit}
     * Return full unit/lesson details and interactive content_blocks for the mobile player.
     */
    public function unitDetail(Request $request, Unit $unit): JsonResponse
    {
        /** @var User|null $user */
        $user = $request->user('sanctum');

        if (!$unit->is_published) {
            return response()->json([
                'success' => false,
                'message' => 'This lesson is not available.',
            ], 404);
        }

        if (!$unit->isAccessibleBy($user)) {
            $requiresAuth = !$user && ($unit->is_registered_only || !$unit->is_free_sample);
            $requiresUpgrade = $user && !$user->hasActiveSubscription();

            return response()->json([
                'success' => false,
                'message' => $requiresAuth
                    ? 'Please sign in to access this lesson.'
                    : 'This interactive lesson requires an active Premium Subscription.',
                'auth_required' => $requiresAuth,
                'upgrade_required' => $requiresUpgrade,
                'unit' => [
                    'id' => $unit->id,
                    'course_session_id' => $unit->course_session_id,
                    'sort_order' => (int) $unit->sort_order,
                    'title' => $unit->title,
                    'is_free_sample' => (bool) $unit->is_free_sample,
                    'is_registered_only' => (bool) $unit->is_registered_only,
                ],
            ], 403);
        }

        $unit->load('courseSession.module');
        $rawTitle = trim((string) $unit->title);
        $displayTitle = ctype_digit($rawTitle) ? "Lesson {$rawTitle}" : $rawTitle;

        $isCompleted = $user
            ? $user->completedUnits()->where('units.id', $unit->id)->exists()
            : false;

        $nextUnit = $unit->nextUnit();
        $prevUnit = $unit->previousUnit();
        $totalSessionUnits = $unit->courseSession
            ? $unit->courseSession->units()->where('is_published', true)->count()
            : 0;

        return $this->noCacheJson([
            'success' => true,
            'unit' => [
                'id' => $unit->id,
                'course_session_id' => $unit->course_session_id,
                'session_title' => $unit->courseSession?->title,
                'module_name' => $unit->courseSession?->module?->name,
                'sort_order' => (int) $unit->sort_order,
                'total_session_units' => (int) $totalSessionUnits,
                'title' => $unit->title,
                'display_title' => $displayTitle,
                'type' => $unit->type,
                'thumbnail_url' => $this->resolveAssetUrl($unit->thumbnail),
                'video_id' => $unit->video_id,
                'audio_url' => $this->resolveAssetUrl($unit->audio_url),
                'transcript' => $unit->transcript,
                'is_free_sample' => (bool) $unit->is_free_sample,
                'is_registered_only' => (bool) $unit->is_registered_only,
                'is_completed' => $isCompleted,
                'content_blocks' => $this->normalizeContentBlocks($unit->content_blocks ?? []),
                'navigation' => [
                    'previous_unit_id' => $prevUnit?->id,
                    'next_unit_id' => $nextUnit?->id,
                ],
                'tts_stream_endpoint' => url('/tts-stream'),
            ],
        ]);
    }

    /**
     * POST /api/units/{unit}/complete
     * Mark a lesson/unit as completed by the authenticated user.
     */
    public function completeUnit(Request $request, Unit $unit): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        if (!$unit->isAccessibleBy($user)) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have access to complete this lesson.',
                'upgrade_required' => !$user->hasActiveSubscription(),
            ], 403);
        }

        $newlyCompleted = false;
        if (!$user->completedUnits()->where('unit_id', $unit->id)->exists()) {
            $user->completedUnits()->attach($unit->id, ['completed_at' => now()]);
            $newlyCompleted = true;

            try {
                $milestoneService = new MilestoneService();
                $milestoneService->checkMilestones($user, $unit);
            } catch (\Throwable $e) {
                Log::warning('Milestone check warning: ' . $e->getMessage());
            }
        }

        $nextUnit = $unit->nextUnit();

        return response()->json([
            'success' => true,
            'message' => 'Lesson marked as completed.',
            'newly_completed' => $newlyCompleted,
            'xp_gained' => $newlyCompleted ? 100 : 0,
            'next_unit_id' => $nextUnit?->id,
            'mastery_streak' => $user->getMasteryStreak(),
            'completed_units_count' => $user->completedUnits()->count(),
        ]);
    }

    /**
     * POST /api/verify-google-purchase
     * Verify a Google Play Billing purchase token and activate/extend the user's subscription.
     */
    public function verifyGooglePurchase(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'purchase_token' => ['required', 'string', 'min:8'],
            'product_id' => ['required', 'string', 'max:191'],
            'plan_id' => ['nullable', 'integer', 'exists:plans,id'],
            'order_id' => ['nullable', 'string', 'max:191'],
            'package_name' => ['nullable', 'string', 'max:191'],
            'purchase_type' => ['nullable', 'string', Rule::in(['subscription', 'inapp', 'product'])],
            'user_id' => ['nullable', 'integer', 'exists:users,id'],
        ]);

        /** @var User|null $user */
        $user = $request->user('sanctum');
        if (!$user && !empty($validated['user_id'])) {
            $user = User::find((int) $validated['user_id']);
        }

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated. Please provide a valid Bearer token or user_id.',
            ], 401);
        }

        $purchaseToken = trim($validated['purchase_token']);
        $productId = trim($validated['product_id']);
        $packageName = trim((string) ($validated['package_name'] ?? config('services.google_play.package_name', 'in.amter.app')));
        $purchaseType = $validated['purchase_type'] ?? 'subscription';
        $orderId = !empty($validated['order_id'])
            ? trim($validated['order_id'])
            : 'gplay_' . substr(hash('sha256', $purchaseToken), 0, 24);

        // 1. Idempotency Check: Check if this purchase_token or order_id was already processed
        $existingPayment = Payment::where(function ($q) use ($purchaseToken, $orderId) {
            $q->where('signature', $purchaseToken)
              ->orWhere('order_id', $orderId);
        })
            ->where('status', 'completed')
            ->first();

        if ($existingPayment) {
            if ((int) $existingPayment->user_id !== (int) $user->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'This Google Play purchase token has already been claimed by another account.',
                ], 409);
            }

            $user->load('plan');

            return response()->json([
                'success' => true,
                'already_verified' => true,
                'message' => 'Purchase already verified and subscription is active.',
                'subscription' => [
                    'has_active_subscription' => $user->hasActiveSubscription(),
                    'membership_type' => $user->membership_type,
                    'subscription_expires_at' => $user->subscription_expires_at?->toIso8601String(),
                    'plan' => $user->plan ? $this->formatPlan($user->plan) : null,
                ],
                'payment' => [
                    'id' => $existingPayment->id,
                    'order_id' => $existingPayment->order_id,
                    'product_id' => $existingPayment->payment_id,
                    'amount' => (float) $existingPayment->amount,
                    'currency' => $existingPayment->currency ?? 'INR',
                    'method' => $existingPayment->method,
                    'status' => $existingPayment->status,
                ],
                'user' => $this->formatUser($user),
            ]);
        }

        // 2. Resolve the corresponding Plan
        $plan = $this->resolvePlanFromGoogleProduct(
            $validated['plan_id'] ?? null,
            $productId
        );

        if (!$plan) {
            return response()->json([
                'success' => false,
                'message' => "Unable to match Google Play product '{$productId}' to an active subscription plan.",
            ], 422);
        }

        // 3. Verify with Google Play Developer API if Service Account credentials are configured
        $verificationResult = $this->verifyWithGooglePlayApi(
            packageName: $packageName,
            productId: $productId,
            purchaseToken: $purchaseToken,
            purchaseType: $purchaseType
        );

        if (!$verificationResult['valid']) {
            Payment::create([
                'user_id' => $user->id,
                'plan_id' => $plan->id,
                'order_id' => $orderId,
                'payment_id' => $productId,
                'signature' => $purchaseToken,
                'receipt' => 'gplay_' . $plan->id . '_' . $user->id . '_' . time(),
                'amount' => $plan->price,
                'currency' => 'INR',
                'method' => 'google_play',
                'status' => 'failed',
                'error_reason' => $verificationResult['reason'] ?? 'Google Play purchase verification failed.',
            ]);

            return response()->json([
                'success' => false,
                'message' => $verificationResult['reason'] ?? 'Google Play purchase verification failed.',
            ], 400);
        }

        // 4. Fulfill Subscription & Record Payment in a Transaction
        $payment = DB::transaction(function () use ($user, $plan, $orderId, $productId, $purchaseToken, $verificationResult) {
            $payment = Payment::updateOrCreate(
                ['order_id' => $orderId],
                [
                    'user_id' => $user->id,
                    'plan_id' => $plan->id,
                    'payment_id' => $productId,
                    'signature' => $purchaseToken,
                    'receipt' => 'gplay_' . $plan->id . '_' . $user->id . '_' . time(),
                    'amount' => $plan->price,
                    'currency' => 'INR',
                    'method' => 'google_play',
                    'status' => 'completed',
                    'error_reason' => null,
                ]
            );

            if (!empty($verificationResult['expiry_time'])) {
                $googleExpiry = Carbon::parse($verificationResult['expiry_time']);
                $currentExpiry = $user->subscription_expires_at;
                $user->subscription_expires_at = ($currentExpiry && $currentExpiry->gt($googleExpiry))
                    ? $currentExpiry
                    : $googleExpiry;
            } else {
                $currentExpiry = $user->subscription_expires_at;
                if ($currentExpiry && $currentExpiry->isFuture()) {
                    $user->subscription_expires_at = $currentExpiry->copy()->addDays($plan->duration_days);
                } else {
                    $user->subscription_expires_at = Carbon::now()->addDays($plan->duration_days);
                }
            }

            $user->plan_id = $plan->id;
            $user->save();

            return $payment;
        });

        $user->load('plan');

        Log::info('Google Play purchase verified & subscription fulfilled', [
            'user_id' => $user->id,
            'plan_id' => $plan->id,
            'product_id' => $productId,
            'order_id' => $orderId,
            'mode' => $verificationResult['mode'],
        ]);

        return response()->json([
            'success' => true,
            'already_verified' => false,
            'message' => 'Google Play purchase verified and subscription activated.',
            'verification_mode' => $verificationResult['mode'],
            'subscription' => [
                'has_active_subscription' => $user->hasActiveSubscription(),
                'membership_type' => $user->membership_type,
                'subscription_expires_at' => $user->subscription_expires_at?->toIso8601String(),
                'plan' => $this->formatPlan($plan),
            ],
            'payment' => [
                'id' => $payment->id,
                'order_id' => $payment->order_id,
                'product_id' => $payment->payment_id,
                'amount' => (float) $payment->amount,
                'currency' => $payment->currency ?? 'INR',
                'method' => $payment->method,
                'status' => $payment->status,
            ],
            'user' => $this->formatUser($user),
        ]);
    }

    /**
     * Format User model for JSON responses.
     */
    protected function formatUser(User $user, bool $includeProgress = false): array
    {
        $payload = [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->role ?? 'student',
            'level' => $user->level,
            'is_suspended' => (bool) $user->is_suspended,
            'has_active_subscription' => $user->hasActiveSubscription(),
            'is_paid' => $user->isPaid(),
            'membership_type' => $user->membership_type,
            'subscription_expires_at' => $user->subscription_expires_at?->toIso8601String(),
            'plan' => $user->plan ? $this->formatPlan($user->plan) : null,
            'created_at' => $user->created_at?->toIso8601String(),
        ];

        if ($includeProgress) {
            $nextUnit = $user->getNextIncompleteUnit();
            $payload['stats'] = [
                'completed_units_count' => $user->completedUnits()->count(),
                'mastery_streak_days' => $user->getMasteryStreak(),
                'resume_unit_id' => $nextUnit?->id,
                'resume_unit_title' => $nextUnit?->title,
                'resume_session_id' => $nextUnit?->course_session_id,
            ];
        }

        return $payload;
    }

    /**
     * Format Plan model as a Digital Product for mobile clients.
     */
    protected function formatPlan(Plan $plan): array
    {
        $days = (int) $plan->duration_days;
        $price = (float) $plan->price;

        return [
            'id' => $plan->id,
            'product_id' => "amter_{$days}_days",
            'google_product_id' => "amter_plan_{$plan->id}",
            'name' => $plan->name,
            'price' => $price,
            'currency' => 'INR',
            'duration_days' => $days,
            'effective_daily_price' => $days > 0 ? round($price / $days, 2) : $price,
            'discount_percentage' => round(Plan::getDiscountPercentageForDays($days), 1),
            'is_best_value' => (bool) $plan->is_best_value,
            'is_active' => (bool) $plan->is_active,
            'description' => "Full unlimited access to all Amter Spoken English classes and AI voice matching for {$days} " . ($days === 1 ? 'day' : 'days') . '.',
        ];
    }

    /**
     * Format CourseSession summary for Library / Dashboard listings.
     */
    protected function formatSessionSummary(CourseSession $session, array $completedUnitIds = []): array
    {
        $publishedUnits = $session->relationLoaded('units')
            ? $session->units
            : $session->units()->where('is_published', true)->orderBy('sort_order', 'asc')->get();

        $totalUnits = $publishedUnits->count();
        $completedCount = 0;
        $resumeUnit = $publishedUnits->first();

        foreach ($publishedUnits as $u) {
            if (isset($completedUnitIds[$u->id])) {
                $completedCount++;
            } elseif ($resumeUnit && isset($completedUnitIds[$resumeUnit->id])) {
                $resumeUnit = $u;
            }
        }

        $progressPercent = $totalUnits > 0 ? (int) round(($completedCount / $totalUnits) * 100) : 0;

        return [
            'id' => $session->id,
            'module_id' => $session->module_id,
            'module_name' => $session->module?->name,
            'title' => $session->title,
            'hide_title_on_card' => (bool) $session->is_hidden,
            'slug' => $session->slug,
            'description' => $session->description,
            'duration_minutes' => $session->duration_minutes,
            'thumbnail_url' => $this->resolveAssetUrl($session->thumbnail_path),
            'sort_order' => (int) $session->sort_order,
            'units_count' => $totalUnits,
            'completed_units_count' => $completedCount,
            'progress_percent' => $progressPercent,
            'has_free_sample' => $publishedUnits->contains('is_free_sample', true),
            'first_unit_id' => $publishedUnits->first()?->id,
            'resume_unit_id' => $resumeUnit?->id,
        ];
    }

    /**
     * Normalize relative storage paths inside content_blocks into full URLs for mobile rendering.
     */
    protected function normalizeContentBlocks(array $blocks): array
    {
        return array_map(function ($block) {
            if (!is_array($block) || empty($block['data']) || !is_array($block['data'])) {
                return $block;
            }

            $type = $block['type'] ?? '';
            $data = $block['data'];

            if ($type === 'image') {
                $cUrl = $data['custom_url'] ?? null;
                if (!empty($data['media_item_selection'])) {
                    $media = MediaItem::find($data['media_item_selection']);
                    if ($media) {
                        $cUrl = $media->path;
                    }
                }
                if (!empty($cUrl) && !str_contains($cUrl, '/') && !str_starts_with($cUrl, 'http')) {
                    $cUrl = 'media-library/' . $cUrl;
                }
                $rawPath = $cUrl ?: ($data['url'] ?? null);
                $data['resolved_image_url'] = $this->resolveAssetUrl($rawPath);
            } elseif ($type === 'video') {
                $rawVideo = $data['video_path'] ?? $data['custom_url'] ?? null;
                if (!$rawVideo && !empty($data['media_item_id'])) {
                    $media = MediaItem::find($data['media_item_id']);
                    $rawVideo = $media?->path;
                }
                $data['resolved_video_url'] = $this->resolveAssetUrl($rawVideo);
            } elseif ($type === 'audio') {
                $rawAudio = $data['custom_url'] ?? $data['url'] ?? null;
                $data['resolved_audio_url'] = $this->resolveAssetUrl($rawAudio);
            }

            $block['data'] = $data;
            return $block;
        }, $blocks);
    }

    /**
     * Convert a storage path or relative path into a full URL.
     */
    protected function resolveAssetUrl(?string $path): ?string
    {
        if (empty($path)) {
            return null;
        }
        $trimmed = trim($path);
        if (str_starts_with($trimmed, 'http://') || str_starts_with($trimmed, 'https://')) {
            return $trimmed;
        }
        $clean = ltrim($trimmed, '/');
        if (str_starts_with($clean, 'storage/')) {
            return asset($clean);
        }
        return asset('storage/' . $clean);
    }

    /**
     * Resolve a Plan model from plan_id or Google Play product_id SKU.
     */
    protected function resolvePlanFromGoogleProduct(?int $planId, string $productId): ?Plan
    {
        if ($planId) {
            $plan = Plan::find($planId);
            if ($plan) {
                return $plan;
            }
        }

        if (ctype_digit($productId)) {
            $plan = Plan::find((int) $productId);
            if ($plan) {
                return $plan;
            }
        }

        if (preg_match('/(?:amter_)?plan_(\d+)/i', $productId, $m)) {
            $plan = Plan::find((int) $m[1]);
            if ($plan) {
                return $plan;
            }
        }

        if (preg_match('/(\d+)_?days?/i', $productId, $m)) {
            $days = (int) $m[1];
            $plan = Plan::where('duration_days', $days)->orderBy('is_active', 'desc')->first();
            if ($plan) {
                return $plan;
            }
        }

        $normalizedSku = strtolower(preg_replace('/[^a-z0-9]/i', '', $productId));
        $plans = Plan::orderBy('is_active', 'desc')->get();
        foreach ($plans as $candidate) {
            $candidateSlug = strtolower(preg_replace('/[^a-z0-9]/i', '', $candidate->name));
            if ($candidateSlug !== '' && (str_contains($normalizedSku, $candidateSlug) || str_contains($candidateSlug, $normalizedSku))) {
                return $candidate;
            }
        }

        return Plan::where('is_active', true)->orderBy('duration_days', 'asc')->first();
    }

    /**
     * Verify purchase token against Google Play Android Publisher API when Service Account credentials are set.
     */
    protected function verifyWithGooglePlayApi(string $packageName, string $productId, string $purchaseToken, string $purchaseType): array
    {
        $serviceAccountConfig = config('services.google_play.service_account_json');

        if (empty($serviceAccountConfig)) {
            return [
                'valid' => true,
                'mode' => 'token_accepted',
                'expiry_time' => null,
            ];
        }

        try {
            $credentials = is_file($serviceAccountConfig)
                ? json_decode((string) file_get_contents($serviceAccountConfig), true)
                : json_decode((string) $serviceAccountConfig, true);

            if (!is_array($credentials) || empty($credentials['client_email']) || empty($credentials['private_key'])) {
                Log::warning('Google Play service account JSON is invalid; falling back to token_accepted mode.');
                return [
                    'valid' => true,
                    'mode' => 'token_accepted',
                    'expiry_time' => null,
                ];
            }

            $accessToken = $this->fetchGoogleAccessToken($credentials);
            if (!$accessToken) {
                return [
                    'valid' => false,
                    'mode' => 'google_api',
                    'reason' => 'Could not authenticate with Google Play Developer API.',
                ];
            }

            if ($purchaseType === 'subscription') {
                $url = "https://androidpublisher.googleapis.com/androidpublisher/v3/applications/{$packageName}/purchases/subscriptionsv2/tokens/{$purchaseToken}";
                $res = Http::withToken($accessToken)->timeout(10)->get($url);

                if (!$res->successful()) {
                    return [
                        'valid' => false,
                        'mode' => 'google_api',
                        'reason' => 'Google Play subscription token verification failed (' . $res->status() . ').',
                    ];
                }

                $body = $res->json();
                $state = $body['subscriptionState'] ?? '';
                $lineItems = $body['lineItems'] ?? [];
                $expiryTime = $lineItems[0]['expiryTime'] ?? null;

                $validStates = ['SUBSCRIPTION_STATE_ACTIVE', 'SUBSCRIPTION_STATE_IN_GRACE_PERIOD'];
                if (!empty($state) && !in_array($state, $validStates, true)) {
                    return [
                        'valid' => false,
                        'mode' => 'google_api',
                        'reason' => "Subscription is not active in Google Play (state: {$state}).",
                    ];
                }

                return [
                    'valid' => true,
                    'mode' => 'google_api',
                    'expiry_time' => $expiryTime,
                ];
            }

            $url = "https://androidpublisher.googleapis.com/androidpublisher/v3/applications/{$packageName}/purchases/products/{$productId}/tokens/{$purchaseToken}";
            $res = Http::withToken($accessToken)->timeout(10)->get($url);

            if (!$res->successful()) {
                return [
                    'valid' => false,
                    'mode' => 'google_api',
                    'reason' => 'Google Play product token verification failed (' . $res->status() . ').',
                ];
            }

            $body = $res->json();
            $purchaseState = (int) ($body['purchaseState'] ?? -1);
            if ($purchaseState !== 0) {
                return [
                    'valid' => false,
                    'mode' => 'google_api',
                    'reason' => 'Google Play product purchase is not in a completed state.',
                ];
            }

            return [
                'valid' => true,
                'mode' => 'google_api',
                'expiry_time' => null,
            ];
        } catch (\Throwable $e) {
            Log::error('Google Play verification exception: ' . $e->getMessage());
            return [
                'valid' => false,
                'mode' => 'google_api',
                'reason' => 'Error communicating with Google Play verification service.',
            ];
        }
    }

    /**
     * Mint a short-lived Google OAuth2 access token using Service Account credentials.
     */
    protected function fetchGoogleAccessToken(array $credentials): ?string
    {
        $now = time();
        $header = $this->base64UrlEncode(json_encode(['alg' => 'RS256', 'typ' => 'JWT']));
        $claimSet = $this->base64UrlEncode(json_encode([
            'iss' => $credentials['client_email'],
            'scope' => 'https://www.googleapis.com/auth/androidpublisher',
            'aud' => $credentials['token_uri'] ?? 'https://oauth2.googleapis.com/token',
            'iat' => $now,
            'exp' => $now + 3600,
        ]));

        $unsignedJwt = $header . '.' . $claimSet;
        $signature = '';
        if (!openssl_sign($unsignedJwt, $signature, $credentials['private_key'], OPENSSL_ALGO_SHA256)) {
            return null;
        }

        $jwt = $unsignedJwt . '.' . $this->base64UrlEncode($signature);

        $response = Http::asForm()->timeout(8)->post($credentials['token_uri'] ?? 'https://oauth2.googleapis.com/token', [
            'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
            'assertion' => $jwt,
        ]);

        return $response->successful() ? ($response->json('access_token') ?: null) : null;
    }

    protected function base64UrlEncode(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    protected function noCacheJson(array $data, int $status = 200): JsonResponse
    {
        return response()->json($data, $status, [
            'Cache-Control' => 'no-store, no-cache, must-revalidate, max-age=0',
            'Pragma' => 'no-cache',
            'Expires' => '0',
        ]);
    }
}
