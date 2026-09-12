<?php


use App\Http\Controllers\UnitController;



use App\Http\Controllers\HomeController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\PaymentController;

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/library', [HomeController::class, 'library'])->name('public.library');
Route::get('/sitemap.xml', [App\Http\Controllers\SitemapController::class, 'index']);
Route::redirect('/student', '/app'); // Redirect legacy path

Route::middleware('web')->group(function () {
    Route::get('/login', [LoginController::class, 'show'])->name('login');
    Route::post('/authenticate', [LoginController::class, 'authenticate'])->name('authenticate');
    Route::redirect('/admin/login', '/login');
    Route::get('/logout', function () {
        auth()->logout();
        request()->session()->invalidate();
        request()->session()->regenerateToken();
        return redirect('/');
    })->name('logout');
});

Route::get('/units/{unit}', [UnitController::class, 'show'])->name('student.units.show');
Route::post('/units/{unit}/complete', [UnitController::class, 'complete'])->name('student.units.complete');

// Public Course Routes
Route::get('/courses/{course}', [App\Http\Controllers\PublicCourseController::class, 'show'])->name('public.course.show');
Route::get('/courses/{course}/units/{unit}', [App\Http\Controllers\PublicCourseController::class, 'showUnit'])->name('public.unit.show');

// Static Pages
Route::get('/about', [App\Http\Controllers\PageController::class, 'about'])->name('about');
Route::get('/terms', [App\Http\Controllers\PageController::class, 'terms'])->name('terms');
Route::get('/privacy', [App\Http\Controllers\PageController::class, 'privacy'])->name('privacy');
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

