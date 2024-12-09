<?php

declare(strict_types=1);

namespace Foodticket\Deliveroo\Enums;

enum WebhookType: string
{
    case POS = 'pos';
    case ORDER_EVENTS = 'order_events';
    case POS_AND_ORDER_EVENTS = 'pos_and_order_events';
}
