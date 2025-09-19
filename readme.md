# Implement the new Deliveroo API
This package allows you to easily make requests to the new Deliveroo API. The full documentation of the Deliveroo API can be found here: https://api-docs.deliveroo.com/v2.0/reference/credentials.

## Requirements

- PHP >= 8.2
- Laravel >= 11.0

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
### Getting orders
To get all orders from a restaurant, you can use the following code:
```php
$deliverooApi = new DeliverooApi();
$deliverooApi->getOrders($brandId, $restaurantId);
```

### Create your own request
If you need to create your own request, you can use the following code:
```php
$deliverooApi = new DeliverooApi();
$deliverooApi->request()->get('https://api.developers.deliveroo.com/order/v1/integrator/brands/{$brand_id}/sites-config');
```

## Webhooks
To start receiving webhooks from Deliveroo, you need to add the following route the `App\Providers\RouteServiceProvider` file:
```php
$this->routes(function () {
    // ...
    Route::deliverooWebhooks();
});
```

## Security Vulnerabilities

If you discover a security vulnerability within this project, please email me via [developer@foodticket.nl](mailto:developer@foodticket.nl).
