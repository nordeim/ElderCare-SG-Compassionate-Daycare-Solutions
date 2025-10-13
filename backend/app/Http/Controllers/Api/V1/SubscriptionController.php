<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Subscription\SubscribeRequest;
use App\Models\Subscription;
use App\Jobs\SyncMailchimpSubscriptionJob;
use Illuminate\Http\Request;

class SubscriptionController extends Controller
{
    public function store(SubscribeRequest $request)
    {
        $data = $request->validated();
        $subscription = Subscription::create(array_merge($data, ['mailchimp_status' => 'pending']));

        // Dispatch job to sync
        SyncMailchimpSubscriptionJob::dispatch($subscription->id);

        return response()->json(['success' => true, 'message' => 'Subscription successful! Please check your email to confirm your subscription.', 'data' => ['subscription_id' => $subscription->id]], 201);
    }

    public function destroy(Request $request)
    {
        $email = $request->input('email');
        $sub = Subscription::where('email', $email)->first();
        if ($sub) {
            $sub->delete();
        }

        return response()->json(['success' => true, 'message' => 'Unsubscribed']);
    }
}
