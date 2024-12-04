<?php

namespace Foodticket\Deliveroo;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;

/**
 * Add and maybe check the hmacSignature
 */
class DeliverooWebhook
{
    protected string $platform = 'deliveroo';

    public function __construct(
        protected string $eventName,
        protected string $locationId,
        protected ?string $resourceId,
        protected array $payload,
    ) {
        //        $this->hmacSignature = Arr::get($payload, 'additionalData.hmacSignature');
    }

    public function platform(): string
    {
        return $this->platform;
    }

    public function eventName(): string
    {
        $eventName = Str::of($this->eventName)->lower()->replace(['.', '_'], '-');

        return 'deliveroo-webhooks.'.$eventName->toString();
    }

    public function locationId(): ?string
    {
        return $this->locationId;
    }

    public function resourceId(): ?string
    {
        return $this->resourceId;
    }

    public function payload(): array
    {
        return $this->payload;
    }

    //    public function hmacSignature(): string
    //    {
    //        return $this->hmacSignature;
    //    }

    public static function fromWebhookEvent(array $payload): self
    {
        $locationId = Arr::get($payload, 'body.order.location_id');
        $resourceId = Arr::get($payload, 'body.order.id');

        return new self(
            $payload['event'],
            $locationId,
            $resourceId,
            $payload
        );
    }
}