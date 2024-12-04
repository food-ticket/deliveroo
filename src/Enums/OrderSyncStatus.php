<?php

declare(strict_types=1);

namespace Foodticket\Deliveroo\Enums;

enum OrderSyncStatus: string
{
    case SUCCEEDED = 'succeeded';
    case FAILED = 'failed';
}
