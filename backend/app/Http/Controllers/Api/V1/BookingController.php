<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Booking\StoreBookingRequest;
use App\Http\Requests\Booking\CancelBookingRequest;
use App\Http\Resources\BookingResource;
use App\Http\Responses\ApiResponse;
use App\Models\Booking;
use App\Services\Booking\BookingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class BookingController extends Controller
{
    public function __construct(
        protected BookingService $bookingService
    ) {
        $this->middleware('auth:sanctum');
    }

    public function index(Request $request): JsonResponse
    {
        $filters = $request->only(['status', 'from_date', 'to_date', 'sort_by', 'sort_order', 'per_page']);

        $bookings = $this->bookingService->getUserBookings(
            $request->user()->id,
            $filters
        );

        return ApiResponse::paginated(
            $bookings,
            BookingResource::class,
            'Bookings retrieved successfully'
        );
    }

    public function adminIndex(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Booking::class);

        $filters = $request->only(['status', 'center_id', 'from_date', 'to_date', 'per_page']);

        $bookings = $this->bookingService->getAllBookings($filters);

        return ApiResponse::paginated(
            $bookings,
            BookingResource::class,
            'Bookings retrieved successfully'
        );
    }

    public function show(string $bookingNumber): JsonResponse
    {
        try {
            $booking = $this->bookingService->getByBookingNumber($bookingNumber);

            $this->authorize('view', $booking);

            return ApiResponse::success(
                new BookingResource($booking),
                'Booking retrieved successfully'
            );
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return ApiResponse::notFound('Booking not found');
        }
    }

    public function store(StoreBookingRequest $request): JsonResponse
    {
        try {
            $booking = $this->bookingService->create(
                $request->user()->id,
                $request->validated()
            );

            return ApiResponse::created(
                new BookingResource($booking),
                'Booking created successfully. You will receive a confirmation email and SMS shortly.'
            );
        } catch (\InvalidArgumentException $e) {
            return ApiResponse::error($e->getMessage(), null, 400);
        } catch (\RuntimeException $e) {
            return ApiResponse::error($e->getMessage(), null, 400);
        } catch (\Exception $e) {
            Log::error('Booking creation failed', [
                'user_id' => $request->user()->id,
                'error' => $e->getMessage(),
            ]);

            return ApiResponse::error(
                'Unable to create booking. Please try again or contact support.',
                null,
                500
            );
        }
    }

    public function update(Request $request, Booking $booking): JsonResponse
    {
        $this->authorize('update', $booking);

        $request->validate([
            'booking_date' => ['sometimes', 'date', 'after:today'],
            'booking_time' => ['sometimes', 'date_format:H:i'],
            'notes' => ['sometimes', 'string', 'max:1000'],
        ]);

        try {
            $updated = $this->bookingService->update(
                $booking->id,
                $request->only(['booking_date', 'booking_time', 'notes'])
            );

            return ApiResponse::success(
                new BookingResource($updated),
                'Booking updated successfully'
            );
        } catch (\RuntimeException $e) {
            return ApiResponse::error($e->getMessage(), null, 400);
        }
    }

    public function destroy(CancelBookingRequest $request, Booking $booking): JsonResponse
    {
        $this->authorize('delete', $booking);

        try {
            $cancelled = $this->bookingService->cancel(
                $booking->id,
                $request->cancellation_reason
            );

            return ApiResponse::success(
                new BookingResource($cancelled),
                'Booking cancelled successfully'
            );
        } catch (\RuntimeException $e) {
            return ApiResponse::error($e->getMessage(), null, 400);
        }
    }
}
