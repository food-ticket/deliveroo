<?php

namespace Foodticket\Deliveroo\Controllers;

use Exception;
use Foodticket\Deliveroo\DeliverooWebhook;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Event;

class WebhookController extends Controller
{
    public function handle(Request $request)
    {
        if (! $this->hasValidSignature($request)) {
            throw new Exception('Invalid signature', 401);
        }

        $webhook = $this->transformWebhookEvent($request);

        try {
            Event::dispatch($webhook->eventName(), $webhook);

            return response()->noContent(200, ['Content-Type' => 'application/json']);
        } catch (Exception $e) {
            throw new Exception('Error handling webhook', 500);
        }

        return response()->json();
    }

    private function transformWebhookEvent(Request $request): DeliverooWebhook
    {
        $payload = $request->all();

        return DeliverooWebhook::fromWebhookEvent($payload);
    }

    private function hasValidSignature(Request $request): bool
    {
        $clientSecret = config('services.deliveroo.webhook_secret');
        $signature = $request->header('X-Deliveroo-Hmac-Sha256');
        $sequenceGuid = $request->header('X-Deliveroo-Sequence-Guid');

        $hash = hash_hmac('sha256', $sequenceGuid . ' ' . $request->getContent(), $clientSecret);

        return hash_equals($signature, $hash);
    }
}
