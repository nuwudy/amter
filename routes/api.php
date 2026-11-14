<?php

use App\Http\Controllers\Api\MobileApiController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Mobile & JSON API Routes (Laravel Sanctum)
|--------------------------------------------------------------------------
|
| All routes here are prefixed with /api automatically by bootstrap/app.php.
|
*/

// Public Authentication Routes
Route::post('/login', [MobileApiController::class, 'login'])->name('api.login');
Route::post('/register', [MobileApiController::class, 'register'])->name('api.register');

// Public / Optional-Auth Digital Products & Learning Content Routes
Route::get('/products', [MobileApiController::class, 'products'])->name('api.products');
Route::get('/plans', [MobileApiController::class, 'products'])->name('api.plans');
Route::get('/content', [MobileApiController::class, 'content'])->name('api.content');
Route::get('/sessions', [MobileApiController::class, 'sessions'])->name('api.sessions.index');
Route::get('/sessions/{session}', [MobileApiController::class, 'sessionDetail'])->name('api.sessions.show');
Route::get('/units/{unit}', [MobileApiController::class, 'unitDetail'])->name('api.units.show');

// Authenticated Routes (Bearer Token via Laravel Sanctum)
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', [MobileApiController::class, 'profile'])->name('api.user');
    Route::get('/profile', [MobileApiController::class, 'profile'])->name('api.profile');
    Route::match(['put', 'patch'], '/profile', [MobileApiController::class, 'updateProfile'])->name('api.profile.update');
    Route::post('/logout', [MobileApiController::class, 'logout'])->name('api.logout');

    // Lesson Progress
    Route::post('/units/{unit}/complete', [MobileApiController::class, 'completeUnit'])->name('api.units.complete');

    // Google Play In-App Billing & Subscription Verification
    Route::post('/verify-google-purchase', [MobileApiController::class, 'verifyGooglePurchase'])->name('api.verify-google-purchase');

    // In-App Account Deletion (Google Play Policy Requirement)
    Route::delete('/user', [MobileApiController::class, 'deleteAccount'])->name('api.user.delete');
    Route::post('/delete-account', [MobileApiController::class, 'deleteAccount'])->name('api.delete-account');
});
