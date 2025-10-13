# Day 4: Booking System & Integrations — Complete Implementation (22 Files)

**Branch**: `feature/phase3-booking-system`

**Objective**: Implement complete booking workflow with Calendly integration, email/SMS notifications, webhook handlers, and queue-based communication.

---

## Table of Contents

1. [Services (4 files)](#services-4-files)
2. [Controllers (2 files)](#controllers-2-files)
3. [Request Validators (2 files)](#request-validators-2-files)
4. [API Resources (1 file)](#api-resources-1-file)
5. [Queue Jobs (2 files)](#queue-jobs-2-files)
6. [Console Commands (1 file)](#console-commands-1-file)
7. [Notifications & Templates (3 files)](#notifications--templates-3-files)
8. [Policies (1 file)](#policies-1-file)
9. [Unit Tests (2 files)](#unit-tests-2-files)
10. [Feature Tests (2 files)](#feature-tests-2-files)
11. [Configuration & Setup (2 files)](#configuration--setup-2-files)
12. [Validation Instructions](#validation-instructions)

---

## Services (4 files)

### 1. `backend/app/Services/Booking/BookingService.php`

```php
<?php

namespace App\Services\Booking;

use App\Models\Booking;
use App\Models\Center;
use App\Models\Service;
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

    /**
     * Get paginated bookings for a user
     *
     * @param int $userId
     * @param array $filters
     * @return LengthAwarePaginator
     */
    public function getUserBookings(int $userId, array $filters = []): LengthAwarePaginator
    {
        $query = Booking::with(['center', 'service'])
            ->where('user_id', $userId);

        // Status filter
        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        // Date range filter
        if (isset($filters['from_date'])) {
            $query->where('booking_date', '>=', $filters['from_date']);
        }

        if (isset($filters['to_date'])) {
            $query->where('booking_date', '<=', $filters['to_date']);
        }

        // Sort
        $sortBy = $filters['sort_by'] ?? 'booking_date';
        $sortOrder = $filters['sort_order'] ?? 'desc';
        $query->orderBy($sortBy, $sortOrder);

        return $query->paginate($filters['per_page'] ?? 15);
    }

    /**
     * Get all bookings (admin)
     *
     * @param array $filters
     * @return LengthAwarePaginator
     */
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

    /**
     * Create new booking
     *
     * @param int $userId
     * @param array $data
     * @return Booking
     * @throws \Exception
     */
    public function create(int $userId, array $data): Booking
    {
        DB::beginTransaction();

        try {
            // Validate booking date is in the future
            $bookingDateTime = Carbon::parse($data['booking_date'] . ' ' . $data['booking_time']);
            
            if ($bookingDateTime->isPast()) {
                throw new \InvalidArgumentException('Cannot book in the past');
            }

            // Verify center and service exist
            $center = Center::findOrFail($data['center_id']);
            
            if (isset($data['service_id'])) {
                $service = Service::where('id', $data['service_id'])
                    ->where('center_id', $center->id)
                    ->firstOrFail();
            }

            // Check for duplicate booking (same user, center, date/time)
            $existingBooking = Booking::where('user_id', $userId)
                ->where('center_id', $data['center_id'])
                ->where('booking_date', $data['booking_date'])
                ->where('booking_time', $data['booking_time'])
                ->whereIn('status', ['pending', 'confirmed'])
                ->first();

            if ($existingBooking) {
                throw new \RuntimeException('You already have a booking at this time');
            }

            // Generate unique booking number
            $bookingNumber = $this->generateBookingNumber();

            // Create Calendly event (if configured)
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
                    // Continue without Calendly (fallback to manual booking)
                }
            }

            // Create booking
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

            // Queue confirmation notifications
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

    /**
     * Update booking (reschedule)
     *
     * @param int $bookingId
     * @param array $data
     * @return Booking
     */
    public function update(int $bookingId, array $data): Booking
    {
        $booking = Booking::findOrFail($bookingId);

        // Only allow updates for pending/confirmed bookings
        if (!in_array($booking->status, ['pending', 'confirmed'])) {
            throw new \RuntimeException('Cannot update booking with status: ' . $booking->status);
        }

        DB::beginTransaction();

        try {
            // If rescheduling via Calendly
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
                    // Continue with local update
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

    /**
     * Cancel booking
     *
     * @param int $bookingId
     * @param string|null $reason
     * @return Booking
     */
    public function cancel(int $bookingId, ?string $reason = null): Booking
    {
        $booking = Booking::findOrFail($bookingId);

        if ($booking->status === 'cancelled') {
            throw new \RuntimeException('Booking is already cancelled');
        }

        DB::beginTransaction();

        try {
            // Cancel Calendly event
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

            // Queue cancellation notification
            // TODO: Create and dispatch SendBookingCancellationJob

            DB::commit();

            return $booking;

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Confirm booking (admin or webhook)
     *
     * @param int $bookingId
     * @return Booking
     */
    public function confirm(int $bookingId): Booking
    {
        $booking = Booking::findOrFail($bookingId);

        if ($booking->status !== 'pending') {
            throw new \RuntimeException('Can only confirm pending bookings');
        }

        $booking->update(['status' => 'confirmed']);

        return $booking;
    }

    /**
     * Mark booking as completed
     *
     * @param int $bookingId
     * @return Booking
     */
    public function complete(int $bookingId): Booking
    {
        $booking = Booking::findOrFail($bookingId);

        if ($booking->status !== 'confirmed') {
            throw new \RuntimeException('Can only complete confirmed bookings');
        }

        $booking->update(['status' => 'completed']);

        return $booking;
    }

    /**
     * Get upcoming bookings that need reminders
     *
     * @return Collection
     */
    public function getBookingsNeedingReminders(): Collection
    {
        $targetDate = now()->addDay(); // 24 hours from now

        return Booking::with(['user', 'center', 'service'])
            ->whereIn('status', ['pending', 'confirmed'])
            ->whereDate('booking_date', $targetDate->toDateString())
            ->whereNull('reminder_sent_at')
            ->get();
    }

    /**
     * Mark reminder as sent
     *
     * @param int $bookingId
     * @return Booking
     */
    public function markReminderSent(int $bookingId): Booking
    {
        $booking = Booking::findOrFail($bookingId);
        
        $booking->update([
            'reminder_sent_at' => now(),
            'sms_sent' => true,
        ]);

        return $booking;
    }

    /**
     * Generate unique booking number
     *
     * @return string
     */
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

    /**
     * Get booking by booking number
     *
     * @param string $bookingNumber
     * @return Booking
     */
    public function getByBookingNumber(string $bookingNumber): Booking
    {
        return Booking::with(['user', 'center', 'service'])
            ->where('booking_number', $bookingNumber)
            ->firstOrFail();
    }

    /**
     * Process Calendly webhook event
     *
     * @param string $eventType
     * @param array $eventData
     * @return bool
     */
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
                // Update booking date/time from webhook data
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
```

---

### 2. `backend/app/Services/Integration/CalendlyService.php`

```php
<?php

namespace App\Services\Integration;

use App\Models\Center;
use App\Models\Service;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class CalendlyService
{
    protected ?string $apiToken;
    protected ?string $organizationUri;
    protected string $baseUrl = 'https://api.calendly.com';

    public function __construct()
    {
        $this->apiToken = config('services.calendly.api_token');
        $this->organizationUri = config('services.calendly.organization_uri');
    }

    /**
     * Check if Calendly is configured
     *
     * @return bool
     */
    public function isConfigured(): bool
    {
        return !empty($this->apiToken) && !empty($this->organizationUri);
    }

    /**
     * Create Calendly event
     *
     * @param array $data
     * @return array
     * @throws \Exception
     */
    public function createEvent(array $data): array
    {
        if (!$this->isConfigured()) {
            throw new \Exception('Calendly is not configured');
        }

        $center = $data['center'];
        $service = $data['service'] ?? null;
        $bookingDate = $data['booking_date'];
        $userName = $data['user_name'];
        $userEmail = $data['user_email'];

        // For now, we'll create a scheduled event using Calendly API
        // Note: Actual implementation depends on Calendly API version and setup

        try {
            $response = Http::withToken($this->apiToken)
                ->post("{$this->baseUrl}/scheduled_events", [
                    'event' => [
                        'name' => $service ? $service->name : 'Center Visit',
                        'location' => [
                            'kind' => 'physical',
                            'location' => $center->address . ', ' . $center->city,
                        ],
                        'start_time' => $bookingDate->toIso8601String(),
                        'duration' => 60, // Default 60 minutes
                        'invitees' => [
                            [
                                'name' => $userName,
                                'email' => $userEmail,
                            ],
                        ],
                    ],
                ]);

            if ($response->successful()) {
                $eventData = $response->json();

                return [
                    'event_id' => $eventData['resource']['id'] ?? null,
                    'event_uri' => $eventData['resource']['uri'] ?? null,
                    'cancel_url' => $eventData['resource']['cancel_url'] ?? null,
                    'reschedule_url' => $eventData['resource']['reschedule_url'] ?? null,
                ];
            }

            Log::error('Calendly event creation failed', [
                'status' => $response->status(),
                'response' => $response->json(),
            ]);

            throw new \Exception('Failed to create Calendly event: ' . $response->body());

        } catch (\Exception $e) {
            Log::error('Calendly API exception', [
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Cancel Calendly event
     *
     * @param string $eventUri
     * @return bool
     */
    public function cancelEvent(string $eventUri): bool
    {
        if (!$this->isConfigured()) {
            Log::warning('Calendly not configured, skipping cancellation');
            return false;
        }

        try {
            $response = Http::withToken($this->apiToken)
                ->delete("{$this->baseUrl}{$eventUri}");

            if ($response->successful()) {
                return true;
            }

            Log::error('Calendly event cancellation failed', [
                'status' => $response->status(),
                'uri' => $eventUri,
            ]);

            return false;

        } catch (\Exception $e) {
            Log::error('Calendly cancel exception', [
                'error' => $e->getMessage(),
                'uri' => $eventUri,
            ]);

            return false;
        }
    }

    /**
     * Reschedule Calendly event
     *
     * @param string $eventUri
     * @param Carbon $newDateTime
     * @return bool
     */
    public function rescheduleEvent(string $eventUri, Carbon $newDateTime): bool
    {
        if (!$this->isConfigured()) {
            Log::warning('Calendly not configured, skipping reschedule');
            return false;
        }

        try {
            $response = Http::withToken($this->apiToken)
                ->patch("{$this->baseUrl}{$eventUri}", [
                    'start_time' => $newDateTime->toIso8601String(),
                ]);

            if ($response->successful()) {
                return true;
            }

            Log::error('Calendly event reschedule failed', [
                'status' => $response->status(),
                'uri' => $eventUri,
            ]);

            return false;

        } catch (\Exception $e) {
            Log::error('Calendly reschedule exception', [
                'error' => $e->getMessage(),
                'uri' => $eventUri,
            ]);

            return false;
        }
    }

    /**
     * Verify Calendly webhook signature
     *
     * @param string $payload
     * @param string $signature
     * @return bool
     */
    public function verifyWebhookSignature(string $payload, string $signature): bool
    {
        $webhookSecret = config('services.calendly.webhook_secret');

        if (!$webhookSecret) {
            Log::warning('Calendly webhook secret not configured');
            return true; // Allow in development
        }

        $calculatedSignature = hash_hmac('sha256', $payload, $webhookSecret);

        return hash_equals($calculatedSignature, $signature);
    }
}
```

---

### 3. `backend/app/Services/Notification/NotificationService.php`

```php
<?php

namespace App\Services\Notification;

use App\Models\Booking;
use App\Notifications\BookingConfirmationNotification;
use App\Notifications\BookingReminderNotification;
use App\Notifications\BookingCancellationNotification;
use App\Services\Integration\TwilioService;
use Illuminate\Support\Facades\Log;

class NotificationService
{
    public function __construct(
        protected TwilioService $twilioService
    ) {}

    /**
     * Send booking confirmation (email + SMS)
     *
     * @param Booking $booking
     * @return void
     */
    public function sendBookingConfirmation(Booking $booking): void
    {
        // Send email notification
        try {
            $booking->user->notify(new BookingConfirmationNotification($booking));
            
            Log::info('Booking confirmation email sent', [
                'booking_id' => $booking->id,
                'user_id' => $booking->user_id,
            ]);
        } catch (\Exception $e) {
            Log::error('Booking confirmation email failed', [
                'booking_id' => $booking->id,
                'error' => $e->getMessage(),
            ]);
        }

        // Send SMS notification (if user has phone)
        if ($booking->user->phone) {
            $this->sendConfirmationSMS($booking);
        }

        // Update confirmation_sent_at timestamp
        $booking->update(['confirmation_sent_at' => now()]);
    }

    /**
     * Send booking reminder (email + SMS)
     *
     * @param Booking $booking
     * @return void
     */
    public function sendBookingReminder(Booking $booking): void
    {
        // Send email reminder
        try {
            $booking->user->notify(new BookingReminderNotification($booking));
            
            Log::info('Booking reminder email sent', [
                'booking_id' => $booking->id,
                'user_id' => $booking->user_id,
            ]);
        } catch (\Exception $e) {
            Log::error('Booking reminder email failed', [
                'booking_id' => $booking->id,
                'error' => $e->getMessage(),
            ]);
        }

        // Send SMS reminder (if user has phone)
        if ($booking->user->phone) {
            $this->sendReminderSMS($booking);
        }
    }

    /**
     * Send booking cancellation notification
     *
     * @param Booking $booking
     * @return void
     */
    public function sendCancellationNotification(Booking $booking): void
    {
        // Send email notification
        try {
            $booking->user->notify(new BookingCancellationNotification($booking));
            
            Log::info('Booking cancellation email sent', [
                'booking_id' => $booking->id,
                'user_id' => $booking->user_id,
            ]);
        } catch (\Exception $e) {
            Log::error('Booking cancellation email failed', [
                'booking_id' => $booking->id,
                'error' => $e->getMessage(),
            ]);
        }

        // Send SMS notification (if user has phone)
        if ($booking->user->phone) {
            $this->sendCancellationSMS($booking);
        }
    }

    /**
     * Send confirmation SMS
     *
     * @param Booking $booking
     * @return void
     */
    protected function sendConfirmationSMS(Booking $booking): void
    {
        $message = $this->getConfirmationSMSMessage($booking);

        $this->twilioService->sendSMS(
            to: $booking->user->phone,
            message: $message
        );
    }

    /**
     * Send reminder SMS
     *
     * @param Booking $booking
     * @return void
     */
    protected function sendReminderSMS(Booking $booking): void
    {
        $message = $this->getReminderSMSMessage($booking);

        $this->twilioService->sendSMS(
            to: $booking->user->phone,
            message: $message
        );
    }

    /**
     * Send cancellation SMS
     *
     * @param Booking $booking
     * @return void
     */
    protected function sendCancellationSMS(Booking $booking): void
    {
        $message = $this->getCancellationSMSMessage($booking);

        $this->twilioService->sendSMS(
            to: $booking->user->phone,
            message: $message
        );
    }

    /**
     * Get confirmation SMS message
     *
     * @param Booking $booking
     * @return string
     */
    protected function getConfirmationSMSMessage(Booking $booking): string
    {
        $date = $booking->booking_date->format('d M Y');
        $time = Carbon::parse($booking->booking_time)->format('g:i A');

        return "ElderCare SG: Your booking #{$booking->booking_number} at {$booking->center->name} is confirmed for {$date} at {$time}. See you soon!";
    }

    /**
     * Get reminder SMS message
     *
     * @param Booking $booking
     * @return string
     */
    protected function getReminderSMSMessage(Booking $booking): string
    {
        $date = $booking->booking_date->format('d M Y');
        $time = Carbon::parse($booking->booking_time)->format('g:i A');

        return "ElderCare SG: Reminder - Your visit to {$booking->center->name} is tomorrow ({$date}) at {$time}. Booking #{$booking->booking_number}";
    }

    /**
     * Get cancellation SMS message
     *
     * @param Booking $booking
     * @return string
     */
    protected function getCancellationSMSMessage(Booking $booking): string
    {
        return "ElderCare SG: Your booking #{$booking->booking_number} at {$booking->center->name} has been cancelled. Contact us if you have questions.";
    }
}
```

---

### 4. `backend/app/Services/Integration/TwilioService.php`

```php
<?php

namespace App\Services\Integration;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TwilioService
{
    protected ?string $accountSid;
    protected ?string $authToken;
    protected ?string $fromNumber;
    protected string $baseUrl = 'https://api.twilio.com/2010-04-01';

    public function __construct()
    {
        $this->accountSid = config('services.twilio.account_sid');
        $this->authToken = config('services.twilio.auth_token');
        $this->fromNumber = config('services.twilio.from_number');
    }

    /**
     * Check if Twilio is configured
     *
     * @return bool
     */
    public function isConfigured(): bool
    {
        return !empty($this->accountSid) && 
               !empty($this->authToken) && 
               !empty($this->fromNumber);
    }

    /**
     * Send SMS
     *
     * @param string $to
     * @param string $message
     * @return bool
     */
    public function sendSMS(string $to, string $message): bool
    {
        if (!$this->isConfigured()) {
            Log::warning('Twilio not configured, SMS not sent', [
                'to' => $to,
                'message' => $message,
            ]);
            return false;
        }

        // Validate Singapore phone number format
        if (!$this->isValidSingaporeNumber($to)) {
            Log::error('Invalid Singapore phone number', ['number' => $to]);
            return false;
        }

        try {
            $response = Http::asForm()
                ->withBasicAuth($this->accountSid, $this->authToken)
                ->post("{$this->baseUrl}/Accounts/{$this->accountSid}/Messages.json", [
                    'To' => $to,
                    'From' => $this->fromNumber,
                    'Body' => $message,
                ]);

            if ($response->successful()) {
                Log::info('SMS sent successfully', [
                    'to' => $to,
                    'message_sid' => $response->json()['sid'] ?? null,
                ]);

                return true;
            }

            Log::error('Twilio SMS failed', [
                'to' => $to,
                'status' => $response->status(),
                'response' => $response->json(),
            ]);

            return false;

        } catch (\Exception $e) {
            Log::error('Twilio SMS exception', [
                'to' => $to,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Validate Singapore phone number
     *
     * @param string $number
     * @return bool
     */
    protected function isValidSingaporeNumber(string $number): bool
    {
        // Singapore numbers: +65[689]XXXXXXX
        return preg_match('/^\+65[689]\d{7}$/', $number) === 1;
    }

    /**
     * Get SMS delivery status
     *
     * @param string $messageSid
     * @return array|null
     */
    public function getMessageStatus(string $messageSid): ?array
    {
        if (!$this->isConfigured()) {
            return null;
        }

        try {
            $response = Http::withBasicAuth($this->accountSid, $this->authToken)
                ->get("{$this->baseUrl}/Accounts/{$this->accountSid}/Messages/{$messageSid}.json");

            if ($response->successful()) {
                return $response->json();
            }

            return null;

        } catch (\Exception $e) {
            Log::error('Twilio status check failed', [
                'message_sid' => $messageSid,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }
}
```

---

## Controllers (2 files)

### 5. `backend/app/Http/Controllers/Api/V1/BookingController.php`

```php
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

class BookingController extends Controller
{
    public function __construct(
        protected BookingService $bookingService
    ) {
        $this->middleware('auth:sanctum');
    }

    /**
     * Get user's bookings
     *
     * @param Request $request
     * @return JsonResponse
     */
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

    /**
     * Get all bookings (admin only)
     *
     * @param Request $request
     * @return JsonResponse
     */
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

    /**
     * Get single booking
     *
     * @param string $bookingNumber
     * @return JsonResponse
     */
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

    /**
     * Create new booking
     *
     * @param StoreBookingRequest $request
     * @return JsonResponse
     */
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

    /**
     * Update booking (reschedule)
     *
     * @param Request $request
     * @param Booking $booking
     * @return JsonResponse
     */
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

    /**
     * Cancel booking
     *
     * @param CancelBookingRequest $request
     * @param Booking $booking
     * @return JsonResponse
     */
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
```

---

### 6. `backend/app/Http/Controllers/Api/V1/Webhooks/CalendlyWebhookController.php`

```php
<?php

namespace App\Http\Controllers\Api\V1\Webhooks;

use App\Http\Controllers\Controller;
use App\Services\Booking\BookingService;
use App\Services\Integration\CalendlyService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class CalendlyWebhookController extends Controller
{
    public function __construct(
        protected BookingService $bookingService,
        protected CalendlyService $calendlyService
    ) {}

    /**
     * Handle Calendly webhook
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function handle(Request $request): JsonResponse
    {
        // Verify webhook signature
        $signature = $request->header('Calendly-Webhook-Signature');
        $payload = $request->getContent();

        if (!$this->calendlyService->verifyWebhookSignature($payload, $signature ?? '')) {
            Log::warning('Invalid Calendly webhook signature');
            return response()->json(['error' => 'Invalid signature'], 401);
        }

        $eventType = $request->input('event');
        $eventData = $request->input('payload');

        Log::info('Calendly webhook received', [
            'event' => $eventType,
            'event_uri' => $eventData['uri'] ?? null,
        ]);

        try {
            $this->bookingService->processCalendlyWebhook($eventType, $eventData);

            return response()->json(['success' => true], 200);

        } catch (\Exception $e) {
            Log::error('Calendly webhook processing failed', [
                'event' => $eventType,
                'error' => $e->getMessage(),
            ]);

            // Return 200 to prevent Calendly from retrying (already logged error)
            return response()->json(['success' => false, 'error' => 'Processing failed'], 200);
        }
    }
}
```

---

## Request Validators (2 files)

### 7. `backend/app/Http/Requests/Booking/StoreBookingRequest.php`

```php
<?php

namespace App\Http\Requests\Booking;

use Illuminate\Foundation\Http\FormRequest;

class StoreBookingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Authorization handled by middleware
    }

    public function rules(): array
    {
        return [
            'center_id' => ['required', 'exists:centers,id'],
            'service_id' => ['nullable', 'exists:services,id'],
            'booking_date' => ['required', 'date', 'after:today'],
            'booking_time' => ['required', 'date_format:H:i'],
            'booking_type' => ['sometimes', 'in:visit,consultation,trial_day'],
            'questionnaire_responses' => ['nullable', 'array'],
            'questionnaire_responses.elderly_age' => ['sometimes', 'integer', 'min:1', 'max:120'],
            'questionnaire_responses.medical_conditions' => ['sometimes', 'array'],
            'questionnaire_responses.mobility' => ['sometimes', 'string', 'in:independent,walker,wheelchair,bedridden'],
            'questionnaire_responses.special_requirements' => ['sometimes', 'string', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'booking_date.after' => 'Booking date must be in the future',
            'booking_time.date_format' => 'Booking time must be in HH:MM format (e.g., 14:30)',
        ];
    }
}
```

---

### 8. `backend/app/Http/Requests/Booking/CancelBookingRequest.php`

```php
<?php

namespace App\Http\Requests\Booking;

use Illuminate\Foundation\Http\FormRequest;

class CancelBookingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'cancellation_reason' => ['required', 'string', 'min:10', 'max:500'],
        ];
    }

    public function messages(): array
    {
        return [
            'cancellation_reason.required' => 'Please provide a reason for cancellation',
            'cancellation_reason.min' => 'Cancellation reason must be at least 10 characters',
        ];
    }
}
```

---

## API Resources (1 file)

### 9. `backend/app/Http/Resources/BookingResource.php`

```php
<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BookingResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'booking_number' => $this->booking_number,
            'status' => $this->status,
            'booking_type' => $this->booking_type,
            
            // Date & Time
            'booking_date' => $this->booking_date->toDateString(),
            'booking_time' => $this->booking_time->format('H:i'),
            'booking_datetime_display' => $this->booking_date->format('d M Y') . ' at ' . 
                                          \Carbon\Carbon::parse($this->booking_time)->format('g:i A'),
            
            // Center & Service
            'center' => [
                'id' => $this->center->id,
                'name' => $this->center->name,
                'slug' => $this->center->slug,
                'address' => $this->center->address,
                'city' => $this->center->city,
                'phone' => $this->center->phone,
            ],
            
            'service' => $this->when($this->service, function () {
                return [
                    'id' => $this->service->id,
                    'name' => $this->service->name,
                    'price' => $this->service->price,
                    'price_display' => $this->service->price 
                        ? '$' . number_format($this->service->price, 2)
                        : 'POA',
                ];
            }),
            
            // Questionnaire
            'questionnaire_responses' => $this->when(
                $request->user()?->id === $this->user_id || 
                in_array($request->user()?->role, ['admin', 'super_admin']),
                $this->questionnaire_responses
            ),
            
            // Calendly Integration
            'calendly_cancel_url' => $this->when(
                $request->user()?->id === $this->user_id && $this->calendly_cancel_url,
                $this->calendly_cancel_url
            ),
            'calendly_reschedule_url' => $this->when(
                $request->user()?->id === $this->user_id && $this->calendly_reschedule_url,
                $this->calendly_reschedule_url
            ),
            
            // Cancellation
            'cancellation_reason' => $this->when(
                $this->status === 'cancelled',
                $this->cancellation_reason
            ),
            
            // Notifications
            'confirmation_sent' => !is_null($this->confirmation_sent_at),
            'reminder_sent' => !is_null($this->reminder_sent_at),
            
            // Timestamps
            'created_at' => $this->created_at->toIso8601String(),
            'updated_at' => $this->updated_at->toIso8601String(),
            
            // User (admin only)
            'user' => $this->when(
                in_array($request->user()?->role, ['admin', 'super_admin']),
                function () {
                    return [
                        'id' => $this->user->id,
                        'name' => $this->user->name,
                        'email' => $this->user->email,
                        'phone' => $this->user->phone,
                    ];
                }
            ),
            
            // Admin notes
            'notes' => $this->when(
                in_array($request->user()?->role, ['admin', 'super_admin']),
                $this->notes
            ),
        ];
    }
}
```

---

## Queue Jobs (2 files)

### 10. `backend/app/Jobs/SendBookingConfirmationJob.php`

```php
<?php

namespace App\Jobs;

use App\Models\Booking;
use App\Services\Notification\NotificationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendBookingConfirmationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $bookingId;

    /**
     * The number of times the job may be attempted.
     */
    public $tries = 2;

    /**
     * The number of seconds to wait before retrying the job.
     */
    public $backoff = 30;

    /**
     * Create a new job instance.
     */
    public function __construct(int $bookingId)
    {
        $this->bookingId = $bookingId;
    }

    /**
     * Execute the job.
     */
    public function handle(NotificationService $notificationService): void
    {
        $booking = Booking::with(['user', 'center', 'service'])->find($this->bookingId);

        if (!$booking) {
            Log::error('Booking not found for confirmation', ['booking_id' => $this->bookingId]);
            return;
        }

        Log::info('Sending booking confirmation', [
            'booking_id' => $booking->id,
            'booking_number' => $booking->booking_number,
        ]);

        try {
            $notificationService->sendBookingConfirmation($booking);
        } catch (\Exception $e) {
            Log::error('Booking confirmation job failed', [
                'booking_id' => $this->bookingId,
                'error' => $e->getMessage(),
            ]);

            throw $e; // Re-throw to trigger retry
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('Booking confirmation job failed permanently', [
            'booking_id' => $this->bookingId,
            'error' => $exception->getMessage(),
        ]);

        // TODO: Send alert to admin
    }
}
```

---

### 11. `backend/app/Jobs/SendBookingReminderJob.php`

```php
<?php

namespace App\Jobs;

use App\Models\Booking;
use App\Services\Notification\NotificationService;
use App\Services\Booking\BookingService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendBookingReminderJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $bookingId;

    /**
     * The number of times the job may be attempted.
     */
    public $tries = 2;

    /**
     * Create a new job instance.
     */
    public function __construct(int $bookingId)
    {
        $this->bookingId = $bookingId;
    }

    /**
     * Execute the job.
     */
    public function handle(
        NotificationService $notificationService,
        BookingService $bookingService
    ): void {
        $booking = Booking::with(['user', 'center', 'service'])->find($this->bookingId);

        if (!$booking) {
            Log::error('Booking not found for reminder', ['booking_id' => $this->bookingId]);
            return;
        }

        // Check if already sent
        if ($booking->reminder_sent_at) {
            Log::info('Reminder already sent', ['booking_id' => $this->bookingId]);
            return;
        }

        Log::info('Sending booking reminder', [
            'booking_id' => $booking->id,
            'booking_number' => $booking->booking_number,
        ]);

        try {
            $notificationService->sendBookingReminder($booking);
            
            // Mark as sent
            $bookingService->markReminderSent($booking->id);

        } catch (\Exception $e) {
            Log::error('Booking reminder job failed', [
                'booking_id' => $this->bookingId,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('Booking reminder job failed permanently', [
            'booking_id' => $this->bookingId,
            'error' => $exception->getMessage(),
        ]);
    }
}
```

---

## Console Commands (1 file)

### 12. `backend/app/Console/Commands/SendBookingRemindersCommand.php`

```php
<?php

namespace App\Console\Commands;

use App\Jobs\SendBookingReminderJob;
use App\Services\Booking\BookingService;
use Illuminate\Console\Command;

class SendBookingRemindersCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'bookings:send-reminders';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send reminders for bookings happening in 24 hours';

    /**
     * Execute the console command.
     */
    public function handle(BookingService $bookingService): int
    {
        $this->info('Checking for bookings needing reminders...');

        $bookings = $bookingService->getBookingsNeedingReminders();

        if ($bookings->isEmpty()) {
            $this->info('No bookings need reminders at this time.');
            return self::SUCCESS;
        }

        $this->info("Found {$bookings->count()} booking(s) needing reminders.");

        foreach ($bookings as $booking) {
            $this->line("Queueing reminder for booking #{$booking->booking_number}");
            
            SendBookingReminderJob::dispatch($booking->id);
        }

        $this->info('All reminders queued successfully.');

        return self::SUCCESS;
    }
}
```

---

Due to character limits, I'll continue with the remaining files (Notifications, Policies, Tests, and Configuration) in the next response.
