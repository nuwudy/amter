<?php


use App\Http\Controllers\UnitController;



use App\Http\Controllers\HomeController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\PaymentController;

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/library', [HomeController::class, 'library'])->name('public.library');
Route::get('/tts-stream', [HomeController::class, 'ttsStream'])->name('tts.stream');
Route::get('/sitemap.xml', [App\Http\Controllers\SitemapController::class, 'index']);
Route::get('/google7d9d49afe342ab69.html', fn () => response('google-site-verification: google7d9d49afe342ab69.html', 200)->header('Content-Type', 'text/html'));
Route::redirect('/student', '/app'); // Redirect legacy path

Route::middleware('web')->group(function () {
    Route::get('/login', [LoginController::class, 'show'])->name('login');
    Route::post('/authenticate', [LoginController::class, 'authenticate'])->name('authenticate');
    Route::get('/register', [\App\Http\Controllers\Auth\RegisterController::class, 'show'])->name('register');
    Route::post('/register', [\App\Http\Controllers\Auth\RegisterController::class, 'register'])->name('register.submit');
    Route::redirect('/admin/login', '/login');
    Route::get('/logout', function () {
        if (auth()->check()) {
            \App\Http\Middleware\SingleDeviceLockdown::unregisterCurrentDevice(auth()->user(), request());
        }
        auth()->logout();
        request()->session()->invalidate();
        request()->session()->regenerateToken();
        return redirect('/');
    })->name('logout');
});

Route::get('/units/{unit}', [UnitController::class, 'show'])->name('student.units.show');
Route::post('/units/{unit}/complete', [UnitController::class, 'complete'])->name('student.units.complete');

// Course Session Jump & Search Routes (default when studying from Library / Dashboard)
Route::get('/sessions/{session}/jump/{order}', function (\App\Models\CourseSession $session, $order) {
    $unit = $session->units()
        ->where('is_published', true)
        ->where('sort_order', (int) $order)
        ->first();

    if (!$unit) {
        return back()->with('warning', "Lesson {$order} not found in {$session->title}.");
    }

    return redirect()->route('student.units.show', ['unit' => $unit->id]);
})->name('sessions.jump');

Route::get('/sessions/{session}/search', function (\App\Models\CourseSession $session, \Illuminate\Http\Request $request) {
    $query = trim($request->query('q', ''));
    $completedUnitIds = auth()->check() ? auth()->user()->completedUnits()->pluck('units.id')->flip()->toArray() : [];

    if (empty($query)) {
        $units = $session->units()
            ->with('courseSession')
            ->where('is_published', true)
            ->orderBy('sort_order', 'asc')
            ->limit(40)
            ->get();
    } else {
        $lessonNumber = null;
        if (preg_match('/^(?:lesson|step|unit)?\s*(\d+)$/i', $query, $m)) {
            $lessonNumber = (int) $m[1];
        }

        // Search inside current session first, and also allow matching lessons across other sessions by keyword
        $units = \App\Models\Unit::with('courseSession')
            ->where('is_published', true)
            ->where(function ($q) use ($session, $query, $lessonNumber) {
                if ($lessonNumber !== null) {
                    $q->where(function ($sq) use ($session, $lessonNumber) {
                        $sq->where('course_session_id', $session->id)
                           ->where('sort_order', $lessonNumber);
                    });
                }
                $q->orWhere('title', 'like', "%{$query}%")
                  ->orWhereHas('courseSession', function ($csq) use ($query) {
                      $csq->where('title', 'like', "%{$query}%");
                  });
            })
            ->orderByRaw('CASE WHEN course_session_id = ' . (int) $session->id . ' THEN 0 ELSE 1 END')
            ->orderBy('sort_order', 'asc')
            ->limit(40)
            ->get();
    }

    return response()->json($units->map(function ($unit) use ($completedUnitIds) {
        $rawTitle = trim((string) ($unit->title ?? 'Untitled Lesson'));
        $displayTitle = ctype_digit($rawTitle) ? "Lesson {$rawTitle}" : $rawTitle;
        $isAdmin = auth()->check() && auth()->user()->isAdmin();

        return [
            'step' => (int) $unit->sort_order,
            'title' => $displayTitle,
            'session' => (string) ($unit->courseSession?->title ?? 'Amter Session'),
            'completed' => isset($completedUnitIds[$unit->id]),
            'url' => route('student.units.show', ['unit' => $unit->id]),
            'edit_url' => $isAdmin
                ? (\App\Filament\Resources\UnitResource::getUrl('edit', ['record' => $unit->id], panel: 'admin') . '?session_id=' . $unit->course_session_id)
                : null,
        ];
    }));
})->name('sessions.search');

// Master Track Jump & Search Routes
Route::get('/tracks/{track}/jump/{step}', function (\App\Models\LearningTrack $track, $step) {
    $trackUnit = $track->trackUnits()
        ->where('step_number', (int) $step)
        ->first();

    if (!$trackUnit) {
        return back()->with('warning', "Step {$step} not found in this track.");
    }

    return redirect()->route('student.units.show', [
        'unit' => $trackUnit->unit_id,
        'track_id' => $track->id,
    ]);
})->name('tracks.jump');

Route::get('/tracks/{track}/search', function (\App\Models\LearningTrack $track, \Illuminate\Http\Request $request) {
    $query = trim($request->query('q', ''));
    if (empty($query)) {
        $steps = $track->trackUnits()
            ->with(['unit', 'unit.courseSession'])
            ->limit(25)
            ->get();
    } else {
        $steps = $track->searchSteps($query);
    }

    $completedUnitIds = auth()->check() ? auth()->user()->completedUnits()->pluck('units.id')->flip()->toArray() : [];
    $isAdmin = auth()->check() && auth()->user()->isAdmin();

    return response()->json($steps->map(function ($step) use ($track, $completedUnitIds, $isAdmin) {
        $unit = $step->unit;
        return [
            'step' => $step->step_number,
            'title' => (string) ($step->title_override ?: ($unit?->title ?? 'Untitled Lesson')),
            'session' => (string) ($unit?->courseSession?->title ?? 'Amter Module'),
            'completed' => isset($completedUnitIds[$step->unit_id]),
            'url' => route('student.units.show', [
                'unit' => $step->unit_id,
                'track_id' => $track->id,
            ]),
            'edit_url' => ($isAdmin && $step->unit_id)
                ? (\App\Filament\Resources\UnitResource::getUrl('edit', ['record' => $step->unit_id], panel: 'admin') . ($unit?->course_session_id ? '?session_id=' . $unit->course_session_id : ''))
                : null,
        ];
    }));
})->name('tracks.search');

// Public Course Routes
Route::get('/courses/{course}', [App\Http\Controllers\PublicCourseController::class, 'show'])->name('public.course.show');
Route::get('/courses/{course}/units/{unit}', [App\Http\Controllers\PublicCourseController::class, 'showUnit'])->name('public.unit.show');

// Static Pages & Google Play Compliance Routes
Route::get('/about', [App\Http\Controllers\PageController::class, 'about'])->name('about');
Route::get('/terms', [App\Http\Controllers\PageController::class, 'terms'])->name('terms');
Route::get('/privacy', [App\Http\Controllers\PageController::class, 'privacy'])->name('privacy');
Route::get('/privacy-policy', [App\Http\Controllers\PageController::class, 'privacy'])->name('privacy.policy');
Route::get('/delete-account', [App\Http\Controllers\PageController::class, 'deleteAccount'])->name('delete.account');
Route::post('/delete-account', [App\Http\Controllers\PageController::class, 'destroyAccount'])->name('delete.account.submit');
Route::get('/contact', [App\Http\Controllers\PageController::class, 'contact'])->name('contact');
Route::get('/refund-policy', [App\Http\Controllers\PageController::class, 'refund'])->name('refund.policy');

// TEMPORARY: Test route to reset progress
Route::get('/test/reset-progress', function() {
    if (auth()->check()) {
        auth()->user()->completedUnits()->detach();
        return redirect()->route('filament.student.pages.dashboard')->with('success', 'All progress reset! Go test the confetti again.');
    }
    return redirect('/');
});

// Library & Modules
// Library Routes replaced by Filament Pages
Route::get('/student/modules/{module}', [\App\Http\Controllers\LibraryController::class, 'show'])->name('student.modules.show');


// Payment & Pricing Routes
Route::get('/pricing', [PaymentController::class, 'index'])->name('pricing');
Route::post('/payment/webhook', [PaymentController::class, 'webhook'])->name('payment.webhook');

Route::middleware(['auth'])->group(function () {
    Route::post('/payment/initiate/{plan}', [PaymentController::class, 'initiate'])->name('payment.initiate');
    Route::post('/checkout/{plan}', [PaymentController::class, 'initiate'])->name('checkout'); // Backward compatibility
    Route::post('/payment/verify', [PaymentController::class, 'verify'])->name('payment.verify');
    Route::post('/reviews', [\App\Http\Controllers\ReviewController::class, 'store'])->name('reviews.store');
    Route::post('/ai-tutor/chat', [\App\Http\Controllers\AiTutorController::class, 'chat'])->name('ai.chat');
});

