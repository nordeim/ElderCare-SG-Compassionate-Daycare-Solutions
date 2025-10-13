<?php

namespace App\Services\Booking;

use App\Models\Booking;
use App\Models\Center;
use App\Models\Service as CenterServiceModel;
use App\Services\Integration\CalendlyService;
use App\Jobs\SendBookingConfirmationJob;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class BookingService
{
    public function __construct(
        protected CalendlyService $calendlyService
    ) {}

    public function getUserBookings(int $userId, array $filters = []): LengthAwarePaginator
    {
        $query = Booking::with(['center', 'service'])
            ->where('user_id', $userId);

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (isset($filters['from_date'])) {
            $query->where('booking_date', '>=', $filters['from_date']);
        }

        if (isset($filters['to_date'])) {
            $query->where('booking_date', '<=', $filters['to_date']);
        }

        $sortBy = $filters['sort_by'] ?? 'booking_date';
        $sortOrder = $filters['sort_order'] ?? 'desc';
        $query->orderBy($sortBy, $sortOrder);

        return $query->paginate($filters['per_page'] ?? 15);
    }

    public function getAllBookings(array $filters = []): LengthAwarePaginator
    {
        $query = Booking::with(['user', 'center', 'service']);

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (isset($filters['center_id'])) {
            $query->where('center_id', $filters['center_id']);
        }

        if (isset($filters['from_date'])) {
            $query->where('booking_date', '>=', $filters['from_date']);
        }

        if (isset($filters['to_date'])) {
            $query->where('booking_date', '<=', $filters['to_date']);
        }

        return $query->orderBy('booking_date', 'desc')
            ->paginate($filters['per_page'] ?? 15);
    }

    public function create(int $userId, array $data): Booking
    {
        DB::beginTransaction();

        try {
            $bookingDateTime = Carbon::parse($data['booking_date'] . ' ' . $data['booking_time']);

            if ($bookingDateTime->isPast()) {
                throw new \InvalidArgumentException('Cannot book in the past');
            }

            $center = Center::findOrFail($data['center_id']);

            if (isset($data['service_id'])) {
                $service = CenterServiceModel::where('id', $data['service_id'])
                    ->where('center_id', $center->id)
                    ->firstOrFail();
            }

            $existingBooking = Booking::where('user_id', $userId)
                ->where('center_id', $data['center_id'])
                ->where('booking_date', $data['booking_date'])
                ->where('booking_time', $data['booking_time'])
                ->whereIn('status', ['pending', 'confirmed'])
                ->first();

            if ($existingBooking) {
                throw new \RuntimeException('You already have a booking at this time');
            }

            $bookingNumber = $this->generateBookingNumber();

            $calendlyEventData = null;
            if ($this->calendlyService->isConfigured()) {
                try {
                    $calendlyEventData = $this->calendlyService->createEvent([
                        'center' => $center,
                        'service' => $service ?? null,
                        'booking_date' => $bookingDateTime,
                        'user_name' => auth()->user()->name,
                        'user_email' => auth()->user()->email,
                    ]);
                } catch (\Exception $e) {
                    \Log::warning('Calendly event creation failed', [
                        'error' => $e->getMessage(),
                        'booking_number' => $bookingNumber,
                    ]);
                }
            }

            $booking = Booking::create([
                'booking_number' => $bookingNumber,
                'user_id' => $userId,
                'center_id' => $data['center_id'],
                'service_id' => $data['service_id'] ?? null,
                'booking_date' => $data['booking_date'],
                'booking_time' => $data['booking_time'],
                'booking_type' => $data['booking_type'] ?? 'visit',
                'questionnaire_responses' => $data['questionnaire_responses'] ?? null,
                'status' => 'pending',
                'calendly_event_id' => $calendlyEventData['event_id'] ?? null,
                'calendly_event_uri' => $calendlyEventData['event_uri'] ?? null,
                'calendly_cancel_url' => $calendlyEventData['cancel_url'] ?? null,
                'calendly_reschedule_url' => $calendlyEventData['reschedule_url'] ?? null,
            ]);

            SendBookingConfirmationJob::dispatch($booking->id);

            DB::commit();

            return $booking->fresh(['center', 'service']);

        } catch (\Exception $e) {
            DB::rollBack();

            \Log::error('Booking creation failed', [
                'user_id' => $userId,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    public function update(int $bookingId, array $data): Booking
    {
        $booking = Booking::findOrFail($bookingId);

        if (!in_array($booking->status, ['pending', 'confirmed'])) {
            throw new \RuntimeException('Cannot update booking with status: ' . $booking->status);
        }

        DB::beginTransaction();

        try {
            if ($booking->calendly_event_uri && isset($data['booking_date'])) {
                $newDateTime = Carbon::parse($data['booking_date'] . ' ' . $data['booking_time']);

                try {
                    $this->calendlyService->rescheduleEvent(
                        $booking->calendly_event_uri,
                        $newDateTime
                    );
                } catch (\Exception $e) {
                    \Log::warning('Calendly reschedule failed', [
                        'booking_id' => $bookingId,
                        'error' => $e->getMessage(),
                    ]);
                }
            }

            $booking->update($data);

            DB::commit();

            return $booking->fresh(['center', 'service']);

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function cancel(int $bookingId, ?string $reason = null): Booking
    {
        $booking = Booking::findOrFail($bookingId);

        if ($booking->status === 'cancelled') {
            throw new \RuntimeException('Booking is already cancelled');
        }

        DB::beginTransaction();

        try {
            if ($booking->calendly_event_uri) {
                try {
                    $this->calendlyService->cancelEvent($booking->calendly_event_uri);
                } catch (\Exception $e) {
                    \Log::warning('Calendly cancellation failed', [
                        'booking_id' => $bookingId,
                        'error' => $e->getMessage(),
                    ]);
                }
            }

            $booking->update([
                'status' => 'cancelled',
                'cancellation_reason' => $reason,
            ]);

            DB::commit();

            return $booking;

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function confirm(int $bookingId): Booking
    {
        $booking = Booking::findOrFail($bookingId);

        if ($booking->status !== 'pending') {
            throw new \RuntimeException('Can only confirm pending bookings');
        }

        $booking->update(['status' => 'confirmed']);

        return $booking;
    }

    public function complete(int $bookingId): Booking
    {
        $booking = Booking::findOrFail($bookingId);

        if ($booking->status !== 'confirmed') {
            throw new \RuntimeException('Can only complete confirmed bookings');
        }

        $booking->update(['status' => 'completed']);

        return $booking;
    }

    public function getBookingsNeedingReminders(): Collection
    {
        $targetDate = now()->addDay();

        return Booking::with(['user', 'center', 'service'])
            ->whereIn('status', ['pending', 'confirmed'])
            ->whereDate('booking_date', $targetDate->toDateString())
            ->whereNull('reminder_sent_at')
            ->get();
    }

    public function markReminderSent(int $bookingId): Booking
    {
        $booking = Booking::findOrFail($bookingId);

        $booking->update([
            'reminder_sent_at' => now(),
            'sms_sent' => true,
        ]);

        return $booking;
    }

    protected function generateBookingNumber(): string
    {
        $date = now()->format('Ymd');
        $lastBooking = Booking::whereDate('created_at', today())
            ->orderBy('id', 'desc')
            ->first();

        $sequence = $lastBooking 
            ? ((int) substr($lastBooking->booking_number, -4)) + 1
            : 1;

        return 'BK-' . $date . '-' . str_pad($sequence, 4, '0', STR_PAD_LEFT);
    }

    public function getByBookingNumber(string $bookingNumber): Booking
    {
        return Booking::with(['user', 'center', 'service'])
            ->where('booking_number', $bookingNumber)
            ->firstOrFail();
    }

    public function processCalendlyWebhook(string $eventType, array $eventData): bool
    {
        $calendlyEventUri = $eventData['uri'] ?? null;

        if (!$calendlyEventUri) {
            \Log::warning('Calendly webhook missing event URI', ['event_type' => $eventType]);
            return false;
        }

        $booking = Booking::where('calendly_event_uri', $calendlyEventUri)->first();

        if (!$booking) {
            \Log::warning('Calendly webhook for unknown booking', ['uri' => $calendlyEventUri]);
            return false;
        }

        switch ($eventType) {
            case 'invitee.created':
                $this->confirm($booking->id);
                \Log::info('Booking confirmed via Calendly', ['booking_id' => $booking->id]);
                break;

            case 'invitee.canceled':
                $this->cancel($booking->id, 'Cancelled via Calendly');
                \Log::info('Booking cancelled via Calendly', ['booking_id' => $booking->id]);
                break;

            case 'invitee.rescheduled':
                if (isset($eventData['start_time'])) {
                    $newDateTime = Carbon::parse($eventData['start_time']);
                    $booking->update([
                        'booking_date' => $newDateTime->toDateString(),
                        'booking_time' => $newDateTime->toTimeString(),
                    ]);
                    \Log::info('Booking rescheduled via Calendly', ['booking_id' => $booking->id]);
                }
                break;

            default:
                \Log::info('Unhandled Calendly webhook event', ['type' => $eventType]);
        }

        return true;
    }
}
