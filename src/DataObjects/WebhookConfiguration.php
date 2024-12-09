<?php

namespace Foodticket\Deliveroo\DataObjects;

use Foodticket\Deliveroo\Enums\WebhookType;

class WebhookConfiguration
{
    public function __construct(
        public string $location_id,
        public WebhookType $orders_api_webhook_type,
    ) {
    }
}
