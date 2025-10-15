<?php

namespace App\Http\Controllers;

use App\Services\Health\HealthService;
use Illuminate\Http\Request;

class HealthController
{
    public function __invoke(Request $request, HealthService $healthService)
    {
        $detailed = false;

        if ($request->query('detailed')) {
            // only allow detailed when explicitly enabled via env flag
            $enabled = filter_var(env('HEALTH_DETAILED', false), FILTER_VALIDATE_BOOLEAN);
            if ($enabled) {
                // if a token is set require it for detailed output
                $token = env('HEALTH_TOKEN');
                if ($token) {
                    $provided = $request->header('X-Health-Token') ?? $request->query('token');
                    if (hash_equals((string) $token, (string) $provided)) {
                        $detailed = true;
                    }
                } else {
                    $detailed = true;
                }
            }
        }

        $result = $healthService->check(['detailed' => $detailed]);

        $status = $result['ok'] ? 200 : 503;

        return response()->json($result, $status);
    }
}
