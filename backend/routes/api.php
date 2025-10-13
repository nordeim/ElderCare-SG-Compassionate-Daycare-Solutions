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

    // Testimonials
    Route::get('/centers/{center}/testimonials', [\App\Http\Controllers\Api\V1\TestimonialController::class, 'index']);

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
        Route::get('/user/testimonials', [\App\Http\Controllers\Api\V1\TestimonialController::class, 'userTestimonials']);
    });

    // Admin routes
    Route::middleware(['auth:sanctum', 'role:admin,super_admin'])->prefix('admin')->group(function () {
        // Testimonial moderation
        Route::get('/testimonials/pending', [\App\Http\Controllers\Api\V1\Admin\TestimonialModerationController::class, 'index']);
        Route::post('/testimonials/{testimonial}/approve', [\App\Http\Controllers\Api\V1\Admin\TestimonialModerationController::class, 'approve']);
        Route::post('/testimonials/{testimonial}/reject', [\App\Http\Controllers\Api\V1\Admin\TestimonialModerationController::class, 'reject']);
        Route::post('/testimonials/{testimonial}/spam', [\App\Http\Controllers\Api\V1\Admin\TestimonialModerationController::class, 'markAsSpam']);
        Route::delete('/testimonials/{testimonial}', [\App\Http\Controllers\Api\V1\Admin\TestimonialModerationController::class, 'destroy']);
        // Translation management
        Route::get('/translations', [\App\Http\Controllers\Api\V1\TranslationController::class, 'index']);
        Route::post('/translations', [\App\Http\Controllers\Api\V1\TranslationController::class, 'store']);
        Route::put('/translations/{translation}', [\App\Http\Controllers\Api\V1\TranslationController::class, 'update']);
        Route::post('/translations/{translation}/mark-translated', [\App\Http\Controllers\Api\V1\TranslationController::class, 'markTranslated']);
        Route::post('/translations/{translation}/mark-reviewed', [\App\Http\Controllers\Api\V1\TranslationController::class, 'markReviewed']);
        Route::post('/translations/{translation}/publish', [\App\Http\Controllers\Api\V1\TranslationController::class, 'publish']);
        Route::delete('/translations/{translation}', [\App\Http\Controllers\Api\V1\TranslationController::class, 'destroy']);
        Route::get('/translations/coverage', [\App\Http\Controllers\Api\V1\TranslationController::class, 'coverage']);
    // Media management
    Route::post('/media', [\App\Http\Controllers\Api\V1\MediaController::class, 'store']);
    Route::delete('/media/{media}', [\App\Http\Controllers\Api\V1\MediaController::class, 'destroy']);
    Route::post('/media/reorder', [\App\Http\Controllers\Api\V1\MediaController::class, 'reorder']);
    Route::put('/media/{media}', [\App\Http\Controllers\Api\V1\MediaController::class, 'update']);
        // Admin users
        Route::get('/users', [\App\Http\Controllers\Api\V1\Admin\UserController::class, 'index']);
        Route::get('/users/{user}', [\App\Http\Controllers\Api\V1\Admin\UserController::class, 'show']);
        Route::put('/users/{user}', [\App\Http\Controllers\Api\V1\Admin\UserController::class, 'update']);
        Route::delete('/users/{user}', [\App\Http\Controllers\Api\V1\Admin\UserController::class, 'destroy']);
        Route::post('/users/profile', [\App\Http\Controllers\Api\V1\Admin\UserController::class, 'updateProfile']);
        Route::get('/users/consents', [\App\Http\Controllers\Api\V1\Admin\UserController::class, 'getConsents']);
        Route::post('/users/export', [\App\Http\Controllers\Api\V1\Admin\UserController::class, 'exportData']);
        Route::post('/users/request-deletion', [\App\Http\Controllers\Api\V1\Admin\UserController::class, 'requestDeletion']);
        Route::post('/users/cancel-deletion', [\App\Http\Controllers\Api\V1\Admin\UserController::class, 'cancelDeletion']);
        // Admin dashboard
        Route::get('/dashboard', [\App\Http\Controllers\Api\V1\Admin\DashboardController::class, 'index']);
        // Moderation endpoints
        Route::get('/moderation/testimonials/pending', [\App\Http\Controllers\Api\V1\Admin\ModerationController::class, 'pendingTestimonials']);
        Route::post('/moderation/testimonials/{testimonial}/approve', [\App\Http\Controllers\Api\V1\Admin\ModerationController::class, 'approveTestimonial']);
        Route::post('/moderation/testimonials/{testimonial}/reject', [\App\Http\Controllers\Api\V1\Admin\ModerationController::class, 'rejectTestimonial']);
        Route::post('/moderation/testimonials/{testimonial}/spam', [\App\Http\Controllers\Api\V1\Admin\ModerationController::class, 'spamTestimonial']);
        Route::get('/moderation/contacts', [\App\Http\Controllers\Api\V1\Admin\ModerationController::class, 'contactSubmissions']);
        Route::post('/moderation/contacts/{submission}/status', [\App\Http\Controllers\Api\V1\Admin\ModerationController::class, 'updateSubmissionStatus']);
        Route::get('/moderation/statistics', [\App\Http\Controllers\Api\V1\Admin\ModerationController::class, 'statistics']);
    });

    // Admin translation routes accessible via /v1/admin/... already guarded
});
