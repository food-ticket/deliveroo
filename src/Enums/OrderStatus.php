<?php

declare(strict_types=1);

namespace Foodticket\Deliveroo\Enums;

enum OrderStatus: string
{
    case PLACED = 'placed';
    case REJECTED = 'rejected';
    case ACCEPTED = 'accepted';
    case CONFIRMED = 'confirmed';
    case CANCELED = 'canceled';

    public function description(): string
    {
        return match ($this) {
            self::PLACED => 'Order is placed in our system.',
            self::REJECTED => 'Order is rejected by site or automatically rejected due to lack of reaction from the restaurant.',
            self::ACCEPTED => 'Order is accepted by site.',
            self::CONFIRMED => 'For scheduled orders only. Site has confirmed that they started preparing the order.',
            self::CANCELED => 'Order was canceled by site or customer.',
        };
    }
}
