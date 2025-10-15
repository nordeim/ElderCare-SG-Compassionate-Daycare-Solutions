<?php

namespace App\Services\Newsletter;

use MailchimpMarketing\ApiClient;

class MailchimpClientFactory
{
    public static function create(): ApiClient
    {
        $client = new ApiClient();

        $server = env('MAILCHIMP_SERVER');
        $apiKey = env('MAILCHIMP_API_KEY');

        // The SDK expects server prefix to be set as config. If missing, leave unconfigured.
        if ($server) {
            $client->setConfig([
                'server' => $server,
                'apiKey' => $apiKey,
            ]);
        } else {
            // If server not set, the SDK may parse from the apiKey; still set apiKey for safety.
            $client->setConfig([
                'apiKey' => $apiKey,
            ]);
        }

        return $client;
    }
}
