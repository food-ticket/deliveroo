<?php

declare(strict_types=1);

namespace Foodticket\Deliveroo\Endpoints;

use Exception;
use Foodticket\Deliveroo\DataObjects\WebhookConfiguration;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Collection;

trait ManagesIntegrator
{
    /**
     * @see https://api-docs.deliveroo.com/reference/put-sites-config
     *
     * @throws ConnectionException
     */
    public function changeIntegratorWebhooksConfiguration(
        string $brandId,
        array $webhookConfigurations,
    ): Response {
        $this->validateWebhookConfigurations($webhookConfigurations);

        $data = $this->transformWebhookConfigurations($webhookConfigurations);

        return $this->request()->put("/api/v1/integrator/brands/{$brandId}/sites-config", $data->toArray());
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
