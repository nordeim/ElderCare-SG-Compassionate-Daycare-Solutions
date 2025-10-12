<?php

namespace App\Http\Responses;

use Illuminate\Http\JsonResponse;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\Json\ResourceCollection;

class ApiResponse
{
    /**
     * Return a success response.
     *
     * @param  mixed  $data
     * @param  string  $message
     * @param  int  $statusCode
     * @return \Illuminate\Http\JsonResponse
     */
    public static function success($data = [], string $message = 'Success', int $statusCode = 200): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data,
            'errors' => null,
        ], $statusCode);
    }

    /**
     * Return an error response.
     *
     * @param  string  $message
     * @param  array  $errors
     * @param  int  $statusCode
     * @return \Illuminate\Http\JsonResponse
     */
    public static function error(string $message = 'Error', array $errors = [], int $statusCode = 400): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $message,
            'data' => null,
            'errors' => $errors,
        ], $statusCode);
    }

    /**
     * Return a paginated response.
     *
     * @param  \Illuminate\Pagination\LengthAwarePaginator  $paginator
     * @param  string  $resourceClass
     * @return \Illuminate\Http\JsonResponse
     */
    public static function paginated(LengthAwarePaginator $paginator, string $resourceClass): JsonResponse
    {
        $collection = $resourceClass::collection($paginator->items());

        return response()->json([
            'success' => true,
            'message' => 'Data retrieved successfully.',
            'data' => $collection,
            'errors' => null,
            'meta' => [
                'pagination' => [
                    'total' => $paginator->total(),
                    'count' => $paginator->count(),
                    'per_page' => $paginator->perPage(),
                    'current_page' => $paginator->currentPage(),
                    'total_pages' => $paginator->lastPage(),
                    'links' => [
                        'first' => $paginator->url(1),
                        'last' => $paginator->url($paginator->lastPage()),
                        'prev' => $paginator->previousPageUrl(),
                        'next' => $paginator->nextPageUrl(),
                    ],
                ],
            ],
        ]);
    }
}
