<?php

declare(strict_types=1);

namespace Foodticket\Deliveroo\Enums;

enum FulfillmentType: string
{
    case DELIVEROO = 'deliveroo';
    case RESTAURANT = 'restaurant';
    case CUSTOMER = 'customer';
    case TABLE_SERVICE = 'table_service';
}
