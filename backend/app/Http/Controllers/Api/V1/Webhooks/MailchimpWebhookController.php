<?php

namespace App\Http\Controllers\Api\V1\Webhooks;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Log;
use App\Services\Newsletter\MailchimpService;

class MailchimpWebhookController extends Controller
{
    public function handle(Request $request, MailchimpService $mailchimp)
    {
        $secret = env('MAILCHIMP_WEBHOOK_SECRET');

        // Allow short-circuit in development if secret not set and testing explicitly
        $token = $request->query('token');
        if ($secret) {
            if (! $token || $token !== $secret) {
                Log::warning('Mailchimp webhook rejected: invalid token', ['remote' => $request->ip()]);
                return response()->json(['message' => 'invalid token'], 403);
            }
        }

        $payload = $request->all();

        $ok = $mailchimp->handleWebhook($payload);

        if ($ok) {
            return response()->json(['message' => 'ok']);
        }

        return response()->json(['message' => 'failed'], 500);
    }
}
