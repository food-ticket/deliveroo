<?php

declare(strict_types=1);

namespace Foodticket\Deliveroo\Enums;

enum OrderRejectReason: string
{
    case CLOSING_EARLY = 'closing_early';
    case BUSY = 'busy';
    case INGREDIENT_UNAVAILABLE = 'ingredient_unavailable';
    case OTHER = 'other';
}
