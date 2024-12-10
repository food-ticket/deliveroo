<?php

declare(strict_types=1);

namespace Foodticket\Deliveroo\Endpoints;

use Exception;
use Foodticket\Deliveroo\DataObjects\WebhookConfiguration;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Collection;

trait ManagesBrands
{
    /**
     * @see https://api-docs.deliveroo.com/v2.0/reference/get_v2-site-brand-id
     *
     * @throws ConnectionException
     */
    public function getBrandId(string $locationId): ?object
    {
        $response = $this->request()->get("/site/v1/restaurant_locations/{$locationId}");

        return $response->object();
    }

    /**
     * @see https://api-docs.deliveroo.com/v2.0/reference/get-all-brands
     *
     * @throws ConnectionException
     */
    public function getBrands(): ?object
    {
        return $this->request()->get('/site/v1/brands')->object();
    }

    /**
     * @see https://api-docs.deliveroo.com/v2.0/reference/put-sites-config
     *
     * @throws ConnectionException
     */
    public function changeIntegratorWebhooksConfiguration(
        string $brandId,
        array $webhookConfigurations,
    ): Response {
        $this->validateWebhookConfigurations($webhookConfigurations);

        $data = [];
        $data['sites'] = $this->transformWebhookConfigurations($webhookConfigurations)->toArray();

        return $this->request()->put("/order/v1/integrator/brands/{$brandId}/sites-config", $data);
    }

    private function transformWebhookConfigurations(array $webhookConfigurations): Collection
    {
        return collect($webhookConfigurations)->transform(function ($webhookConfiguration) {
            return [
                'location_id' => $webhookConfiguration->location_id,
                'orders_api_webhook_type' => $webhookConfiguration->orders_api_webhook_type->name,
            ];
        });
    }

    private function validateWebhookConfigurations(array $webhookConfigurations): bool
    {
        if (empty($webhookConfigurations)) {
            throw new Exception('Webhook configurations cannot be empty.');
        }

        array_walk($webhookConfigurations, function ($webhookConfiguration) {
            if (! $webhookConfiguration instanceof WebhookConfiguration) {
                throw new Exception('Invalid webhook configuration. Must be an instance of WebhookConfiguration.');
            }
        });

        return true;
    }
}
