<?php

namespace App\Services\Newsletter;

use MailchimpMarketing\ApiClient;

class MailchimpClientFactory
{
    public static function create()
    {
        // If the Mailchimp SDK is not installed in this environment, return a noop client
        if (! class_exists('\MailchimpMarketing\ApiClient')) {
            $noop = new \stdClass();
            $lists = new class {
                public function setListMember($listId, $hash, $body) {
                    // noop: pretend success by returning an array with an id
                    return ['id' => 'mc_noop'];
                }
            };
            $noop->lists = $lists;
            return $noop;
        }

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
