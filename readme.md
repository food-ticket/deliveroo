# Implement the new Deliveroo API
This package allows you to easily make requests to the new Deliveroo API. The full documentation of the Deliveroo API can be found here: https://api-docs.deliveroo.com/v2.0/reference/credentials.

## Requirements

- PHP >= 8.3
- Laravel >= 12.0

## Installation

You can install the package via composer:

```bash
composer require foodticket/deliveroo
```

The package will automatically register itself.

## Configuration
To start using the Deliveroo API you will need a client ID and client secret. You can get these by creating an app on the [Deliveroo Developer Portal](https://developers.deliveroo.com/dashboard).
Add the Client ID and client secret to your .env file:
```php
DELIVEROO_BASE_URL=
DELIVEROO_AUTH_URL=
DELIVEROO_CLIENT_ID=
DELIVEROO_CLIENT_SECRET=
DELIVEROO_WEBHOOK_SECRET=
```

## Making requests

### Orders

#### Get orders
Retrieve a paginated list of orders for a specific restaurant. Returns an object with a `next` cursor and a collection of `orders`.

```php
use Foodticket\Deliveroo\DeliverooApi;

$api = new DeliverooApi();
$result = $api->getOrders($brandId, $restaurantId);

// With optional filters
$result = $api->getOrders(
    brandId: $brandId,
    restaurantId: $restaurantId,
    cursor: $result->next,       // pagination cursor from previous response
    startDate: Carbon::now()->subDay(),
    endDate: Carbon::now(),
);

foreach ($result->orders as $order) {
    // ...
}
```

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `$brandId` | `string` | Yes | Your Deliveroo brand ID |
| `$restaurantId` | `string` | Yes | The restaurant/site ID |
| `$cursor` | `string\|null` | No | Pagination cursor from a previous response |
| `$startDate` | `Carbon\|null` | No | Filter orders from this date (ISO 8601) |
| `$endDate` | `Carbon\|null` | No | Filter orders up to this date (ISO 8601) |

---

#### Get order
Retrieve a single order by its ID.

```php
$order = $api->getOrder($orderId);
```

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `$orderId` | `string` | Yes | The Deliveroo order ID |

---

#### Update order status
Accept, reject, or cancel an order. When rejecting, a `$rejectReason` is required.

```php
use Foodticket\Deliveroo\Enums\OrderStatus;
use Foodticket\Deliveroo\Enums\OrderRejectReason;

// Accept an order
$api->updateOrderStatus($orderId, OrderStatus::ACCEPTED);

// Reject an order (reject reason is required)
$api->updateOrderStatus(
    orderId: $orderId,
    status: OrderStatus::REJECTED,
    rejectReason: OrderRejectReason::INGREDIENT_UNAVAILABLE,
    notes: 'The main ingredient is out of stock.',
);
```

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `$orderId` | `string` | Yes | The Deliveroo order ID |
| `$status` | `OrderStatus` | Yes | The new status (see [`OrderStatus`](#orderstatus)) |
| `$rejectReason` | `OrderRejectReason\|null` | Required when rejecting | Reason for rejection (see [`OrderRejectReason`](#orderrejectreason)) |
| `$notes` | `string\|null` | No | Optional notes sent alongside the status update |

---

#### Sync order status
Inform Deliveroo whether your system successfully received and processed an order.

```php
use Foodticket\Deliveroo\Enums\OrderSyncStatus;
use Foodticket\Deliveroo\Enums\Reason;

// Report successful sync
$api->syncStatusOrder($orderId, OrderSyncStatus::SUCCEEDED, Carbon::now());

// Report failed sync (reason is required)
$api->syncStatusOrder(
    orderId: $orderId,
    syncStatus: OrderSyncStatus::FAILED,
    occurredAt: Carbon::now(),
    reason: Reason::WEBHOOK_FAILED,
    notes: 'The webhook endpoint returned a 500.',
);
```

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `$orderId` | `string` | Yes | The Deliveroo order ID |
| `$syncStatus` | `OrderSyncStatus` | Yes | Sync result (see [`OrderSyncStatus`](#ordersyncstatus)) |
| `$occurredAt` | `Carbon` | Yes | Timestamp when the sync attempt occurred |
| `$reason` | `Reason\|null` | Required when status is `FAILED` | Failure reason (see [`Reason`](#reason)) |
| `$notes` | `string\|null` | No | Optional notes |

---

#### Set order preparation stage
Report the current preparation stage of an order to Deliveroo so it can coordinate courier dispatch.

```php
use Foodticket\Deliveroo\Enums\OrderStage;

$api->setOrderPreparationStage(
    orderId: $orderId,
    stage: OrderStage::IN_KITCHEN,
    occurredAt: Carbon::now(),
);

// Optionally signal an expected delay in minutes (0, 2, 4, 6, 8, or 10)
$api->setOrderPreparationStage(
    orderId: $orderId,
    stage: OrderStage::READY_FOR_COLLECTION,
    occurredAt: Carbon::now(),
    delay: 4,
);
```

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `$orderId` | `string` | Yes | The Deliveroo order ID |
| `$stage` | `OrderStage` | Yes | Current preparation stage (see [`OrderStage`](#orderstage)) |
| `$occurredAt` | `Carbon` | Yes | Timestamp when this stage was reached |
| `$delay` | `int\|null` | No | Expected additional delay in minutes. Allowed values: `0`, `2`, `4`, `6`, `8`, `10` |

---

### Brands

#### Get brand ID
Look up brand information by restaurant location ID.

```php
$brand = $api->getBrandId($locationId);
```

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `$locationId` | `string` | Yes | The Deliveroo restaurant location ID |

---

#### Get all brands
Retrieve all brands associated with your integrator credentials.

```php
$brands = $api->getBrands();
```

---

#### Change integrator webhook configuration
Configure which webhook type each restaurant location should use. Each entry must be a `WebhookConfiguration` object.

```php
use Foodticket\Deliveroo\DataObjects\WebhookConfiguration;
use Foodticket\Deliveroo\Enums\WebhookType;

$api->changeIntegratorWebhooksConfiguration(
    brandId: $brandId,
    webhookConfigurations: [
        new WebhookConfiguration(
            location_id: 'location-123',
            orders_api_webhook_type: WebhookType::POS,
        ),
        new WebhookConfiguration(
            location_id: 'location-456',
            orders_api_webhook_type: WebhookType::ORDER_EVENTS,
        ),
    ],
);
```

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `$brandId` | `string` | Yes | Your Deliveroo brand ID |
| `$webhookConfigurations` | `WebhookConfiguration[]` | Yes | One entry per location (must not be empty) |

---

### Create your own request
If you need to call an endpoint not covered by this package, use the underlying HTTP client directly:

```php
$api = new DeliverooApi();
$response = $api->request()->get('https://api.developers.deliveroo.com/order/v1/integrator/brands/{$brand_id}/sites-config');
```

---

## Enums

### OrderStatus
Used in `updateOrderStatus()`.

| Case | Value | Description |
|------|-------|-------------|
| `PLACED` | `placed` | Order is placed in the system |
| `ACCEPTED` | `accepted` | Order is accepted by the site |
| `CONFIRMED` | `confirmed` | Scheduled orders only — site has confirmed preparation has started |
| `REJECTED` | `rejected` | Order is rejected by the site or auto-rejected due to no response |
| `CANCELED` | `canceled` | Order was canceled by the site or customer |

### OrderRejectReason
Used in `updateOrderStatus()` when status is `REJECTED`.

| Case | Value |
|------|-------|
| `CLOSING_EARLY` | `closing_early` |
| `BUSY` | `busy` |
| `INGREDIENT_UNAVAILABLE` | `ingredient_unavailable` |
| `OTHER` | `other` |

### OrderSyncStatus
Used in `syncStatusOrder()`.

| Case | Value |
|------|-------|
| `SUCCEEDED` | `succeeded` |
| `FAILED` | `failed` |

### OrderStage
Used in `setOrderPreparationStage()`.

| Case | Value | Description |
|------|-------|-------------|
| `IN_KITCHEN` | `in_kitchen` | Cooking has started |
| `READY_FOR_COLLECTION_SOON` | `ready_for_collection_soon` | Food is a maximum of 60s from being ready to collect |
| `READY_FOR_COLLECTION` | `ready_for_collection` | Food is cooked and packaged |
| `COLLECTED` | `collected` | The order has been collected |

### Reason
Used in `syncStatusOrder()` when sync status is `FAILED`.

| Case | Value |
|------|-------|
| `PRICE_MISMATCHED` | `price_mismatched` |
| `POS_ITEM_ID_MISMATCHED` | `pos_item_id_mismatched` |
| `POS_ITEM_ID_NOT_FOUND` | `pos_item_id_not_found` |
| `ITEMS_OUT_OF_STOCK` | `items_out_of_stock` |
| `LOCATION_OFFLINE` | `location_offline` |
| `LOCATION_NOT_SUPPORTED` | `location_not_supported` |
| `UNSUPPORTED_ORDER_TYPE` | `unsupported_order_type` |
| `NO_WEBHOOK_URL` | `no_webhook_url` |
| `WEBHOOK_FAILED` | `webhook_failed` |
| `TIMED_OUT` | `timed_out` |
| `NO_SYNC_CONFIRMATION` | `no_sync_confirmation` |
| `OTHER` | `other` |

### WebhookType
Used in `WebhookConfiguration`.

| Case | Value |
|------|-------|
| `POS` | `pos` |
| `ORDER_EVENTS` | `order_events` |
| `POS_AND_ORDER_EVENTS` | `pos_and_order_events` |

### FulfillmentType

| Case | Value |
|------|-------|
| `DELIVEROO` | `deliveroo` |
| `RESTAURANT` | `restaurant` |
| `CUSTOMER` | `customer` |
| `TABLE_SERVICE` | `table_service` |

---

## Data Objects

### WebhookConfiguration
Used when calling `changeIntegratorWebhooksConfiguration()`.

```php
use Foodticket\Deliveroo\DataObjects\WebhookConfiguration;
use Foodticket\Deliveroo\Enums\WebhookType;

new WebhookConfiguration(
    location_id: 'location-123',
    orders_api_webhook_type: WebhookType::POS,
);
```

| Property | Type | Description |
|----------|------|-------------|
| `location_id` | `string` | The Deliveroo restaurant location ID |
| `orders_api_webhook_type` | `WebhookType` | The webhook type to assign to this location |

---

## Webhooks
To start receiving webhooks from Deliveroo, you need to add the following route to the `App\Providers\RouteServiceProvider` file:
```php
$this->routes(function () {
    // ...
    Route::deliverooWebhooks();
});
```

## Security Vulnerabilities

If you discover a security vulnerability within this project, please email me via [developer@foodticket.nl](mailto:developer@foodticket.nl).
