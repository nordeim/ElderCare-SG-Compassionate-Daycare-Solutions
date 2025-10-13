<?php

namespace App\Http\Middleware;

use App\Http\Responses\ApiResponse;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param array<string> $roles
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        if (! $request->user()) {
            return ApiResponse::unauthorized('Authentication required');
        }

        if (! in_array($request->user()->role, $roles)) {
            return ApiResponse::forbidden(
                'Access denied. Required role: ' . implode(' or ', $roles)
            );
        }

        return $next($request);
    }
}
