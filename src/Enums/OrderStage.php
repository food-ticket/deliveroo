<?php

declare(strict_types=1);

namespace Foodticket\Deliveroo\Enums;

enum OrderStage: string
{
    case IN_KITCHEN = 'in_kitchen';
    case READY_FOR_COLLECTION_SOON = 'ready_for_collection_soon';
    case READY_FOR_COLLECTION = 'ready_for_collection';
    case COLLECTED = 'collected';

    public function description(): string
    {
        return match ($this) {
            self::IN_KITCHEN => 'Cooking has started',
            self::READY_FOR_COLLECTION_SOON => 'Food is a maximum of 60s from being ready to collect',
            self::READY_FOR_COLLECTION => 'Food is cooked and packaged',
            self::COLLECTED => 'The order has been collected',
        };
    }
}
