<?php

namespace Foodticket\Deliveroo\Controllers;

use Foodticket\Deliveroo\DeliverooWebhook;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Log;

class WebhookController extends Controller
{
    public function handle(Request $request)
    {
        Log::alert('Request: '.json_encode($request->all()));
        Log::info('Header: '.json_encode($request->header()));

        // if (! $this->hasValidSignature($request)) {
        //     return response()->json('Invalid signature', 401);
        // }

        $webhook = $this->transformWebhookEvent($request);

        Event::dispatch($webhook->eventName(), $webhook);

        // try {
        // Event::dispatch($webhook->eventName(), $webhook);
        //
        // return response()->noContent(200, ['Content-Type' => 'application/json']);
        // } catch (Exception $e) {
        //     Log::error($e->getMessage());
        //
        //     return response()->json('Error handling webhook', 500);
        // }

        return response()->json();
    }

    private function transformWebhookEvent(Request $request): DeliverooWebhook
    {
        $payload = $request->all();

        return DeliverooWebhook::fromWebhookEvent($payload);
    }

    private function hasValidSignature(Request $request): bool
    {
        $clientSecret = config('services.deliveroo.client_secret');
        $signature = hash_hmac('sha256', $request->getContent(), $clientSecret);

        return hash_equals($request->header('X-!!!!-Signature'), $signature);
    }
}
