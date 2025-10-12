<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LogApiRequest
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        try {
            DB::table('api_request_logs')->insert([
                'user_id' => $request->user() ? $request->user()->id : null,
                'method' => $request->method(),
                'path' => $request->path(),
                'ip_address' => $request->ip(),
                'response_status' => $response->status(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } catch (\Exception $e) {
            // Log to default channel if DB logging fails
            Log::error('Failed to log API request to database: ' . $e->getMessage());
        }

        return $response;
    }
}
