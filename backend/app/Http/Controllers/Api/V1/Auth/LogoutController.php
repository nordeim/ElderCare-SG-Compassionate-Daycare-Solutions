<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Http\Controllers\Controller;
use App\Http\Responses\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LogoutController extends Controller
{
    public function destroy(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        \Log::info('User logged out', [
            'user_id' => $request->user()->id,
            'ip' => $request->ip(),
        ]);

        return ApiResponse::success(null, 'Logout successful');
    }
}
