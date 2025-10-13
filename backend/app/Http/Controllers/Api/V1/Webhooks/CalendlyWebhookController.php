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

    public function handle(Request $request): JsonResponse
    {
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

            return response()->json(['success' => false, 'error' => 'Processing failed'], 200);
        }
    }
}
