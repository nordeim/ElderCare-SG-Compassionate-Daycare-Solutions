<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::prefix('v1')->group(function () {
    // Public routes
    Route::post('/auth/register', [\App\Http\Controllers\Api\V1\Auth\RegisterController::class, 'store']);
    Route::post('/auth/login', [\App\Http\Controllers\Api\V1\Auth\LoginController::class, 'store']);
    Route::post('/auth/password-reset/request', [\App\Http\Controllers\Api\V1\Auth\PasswordResetController::class, 'requestReset']);
    Route::post('/auth/password-reset/reset', [\App\Http\Controllers\Api\V1\Auth\PasswordResetController::class, 'reset']);
    Route::post('/contact', [\App\Http\Controllers\Api\V1\ContactController::class, 'store']);
    Route::post('/subscribe', [\App\Http\Controllers\Api\V1\SubscriptionController::class, 'store']);
    Route::get('/centers', [\App\Http\Controllers\Api\V1\CenterController::class, 'index']);
    Route::get('/centers/{slug}', [\App\Http\Controllers\Api\V1\CenterController::class, 'show']);
    Route::get('/faqs', [\App\Http\Controllers\Api\V1\FAQController::class, 'index']);

    // Protected routes
    Route::middleware(['auth:sanctum', 'verified'])->group(function () {
        Route::post('/auth/logout', [\App\Http\Controllers\Api\V1\Auth\LogoutController::class, 'destroy']);
        Route::get('/user/profile', function (Request $request) {
            // Placeholder for user profile endpoint
        });
        Route::post('/bookings', [\App\Http\Controllers\Api\V1\BookingController::class, 'store']);
        Route::get('/bookings', [\App\Http\Controllers\Api\V1\BookingController::class, 'index']);
        Route::get('/bookings/{bookingNumber}', [\App\Http\Controllers\Api\V1\BookingController::class, 'show']);
        Route::delete('/bookings/{id}', [\App\Http\Controllers\Api\V1\BookingController::class, 'destroy']);
        Route::post('/centers/{centerId}/testimonials', [\App\Http\Controllers\Api\V1\TestimonialController::class, 'store']);
    });

    // Admin routes
    Route::middleware(['auth:sanctum', 'role:admin,super_admin'])->prefix('admin')->group(function () {
        // Placeholder for admin routes
    });
});
