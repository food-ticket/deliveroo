<?php

declare(strict_types=1);

namespace Foodticket\Deliveroo\Enums;

enum Reason: string
{
    case PRICE_MISMATCHED = 'price_mismatched';
    case POS_ITEM_ID_MISMATCHED = 'pos_item_id_mismatched';
    case POS_ITEM_ID_NOT_FOUND = 'pos_item_id_not_found';
    case ITEMS_OUT_OF_STOCK = 'items_out_of_stock';
    case LOCATION_OFFLINE = 'location_offline';
    case LOCATION_NOT_SUPPORTED = 'location_not_supported';
    case UNSUPPORTED_ORDER_TYPE = 'unsupported_order_type';
    case NO_WEBHOOK_URL = 'no_webhook_url';
    case WEBHOOK_FAILED = 'webhook_failed';
    case TIMED_OUT = 'timed_out';
    case OTHER = 'other';
    case NO_SYNC_CONFIRMATION = 'no_sync_confirmation';
}
