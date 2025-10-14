<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\BookingRequest;
use App\Http\Resources\Api\V1\BookingResource;
use App\Services\Booking\BookingService;

class BookingController extends Controller
{
    public function __construct(protected BookingService $bookingService) {}

    public function store(BookingRequest $request)
    {
        $validated = $request->validated();

        try {
            $booking = $this->bookingService->create(auth()->id(), $validated);
            // Return the resource as an unwrapped JSON object at 201 to match API contract used in tests
            return response()->json(
                (new BookingResource($booking->fresh(['center', 'service'])))
                    ->toArray($request),
                201
            );
        } catch (\InvalidArgumentException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        } catch (\RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 409);
        }
    }

    public function index()
    {
        $list = $this->bookingService->getUserBookings(auth()->id());
        return BookingResource::collection($list);
    }
    public function show($bookingNumber)
    {
        $b = $this->bookingService->getByBookingNumber($bookingNumber);
        return new BookingResource($b);
    }

    public function destroy($id)
    {
        $booking = $this->bookingService->cancel($id, 'Cancelled via API');
        return new BookingResource($booking);
    }
}
