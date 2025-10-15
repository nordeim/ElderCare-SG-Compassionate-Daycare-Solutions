<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

use App\Http\Controllers\HealthController;

// Health endpoints (public minimal checks). Detailed output is gated by HEALTH_DETAILED and HEALTH_TOKEN.
Route::get('/health', HealthController::class);
Route::get('/up', HealthController::class);
