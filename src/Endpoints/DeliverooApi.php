<?php

declare(strict_types=1);

namespace Foodticket\Deliveroo\Endpoints;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class DeliverooApi
{
    use ManagesOrders;

    private string $baseUrl;

    private string $authUrl;

    public function __construct()
    {
        /**
         * IMPORTANT!
         * use the correct url, see @https://api-docs.deliveroo.com/v2.0/docs/api-and-webhooks
         */
        $this->baseUrl = config('deliveroo.base_url');
        $this->authUrl = config('deliveroo.auth_url');
    }

    protected function request(): PendingRequest
    {
        $accessToken = $this->getAccessToken();

        return Http::baseUrl($this->baseUrl)
            ->asJson()
            ->acceptJson()
            ->withHeaders([
                'accept' => 'application/json',
                'Authorization' => 'Bearer '.$accessToken,
                'content-type' => 'application/json',
            ]);
    }

    /**
     * Get a client access token that we can use to authenticate against the Deliveroo API
     *
     * @see https://api-docs.deliveroo.com/v2.0/reference/get-access-token
     */
    private function getAccessToken(): ?string
    {
        $cacheKey = 'deliveroo:accessToken';
        $accessToken = Cache::get($cacheKey);

        if (! empty($accessToken)) {
            return $accessToken;
        }

        $response = Http::asForm()
            ->withHeaders([
                'accept' => 'application/json',
                'authorization' => 'Basic '.base64_encode(config('deliveroo.client_id').':'.config('deliveroo.client_secret')),
                'content-type' => 'application/x-www-form-urlencoded',

            ])->post(
                $this->authUrl,
                [
                    'grant_type' => 'client_credentials',
                ]
            );

        if ($response->successful()) {
            $data = $response->json();

            // cache the access token, so we don't have to request a new one every time
            Cache::put(
                $cacheKey,
                $data['access_token'],
                // -30 seconds to prevent possibility of request failing because the token expired last second
                now()->addSeconds($data['expires_in'] - 30)
            );

            return $data['access_token'];
        }

        // log & alert if this request somehow fails, possibly means that our client credentials are outdated
        $response->throw();

        return null;
    }
}
