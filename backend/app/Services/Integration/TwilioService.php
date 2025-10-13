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

    public function isConfigured(): bool
    {
        return !empty($this->accountSid) && 
               !empty($this->authToken) && 
               !empty($this->fromNumber);
    }

    public function sendSMS(string $to, string $message): bool
    {
        if (!$this->isConfigured()) {
            Log::warning('Twilio not configured, SMS not sent', [
                'to' => $to,
                'message' => $message,
            ]);
            return false;
        }

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

    protected function isValidSingaporeNumber(string $number): bool
    {
        return preg_match('/^\+65[689]\d{7}$/', $number) === 1;
    }

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
