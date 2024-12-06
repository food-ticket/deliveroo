<?php

declare(strict_types=1);

namespace Foodticket\Deliveroo\Endpoints;

use App\Models\Order;
use Carbon\Carbon;
use Exception;
use Foodticket\Deliveroo\Enums\OrderRejectReason;
use Foodticket\Deliveroo\Enums\OrderStatus;
use Foodticket\Deliveroo\Enums\OrderStage;
use Foodticket\Deliveroo\Enums\OrderSyncStatus;
use Foodticket\Deliveroo\Enums\Reason;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\Client\Response;

trait ManagesOrders
{
    const array ORDER_DELAY_OPTIONS = [0, 2, 4, 6, 8, 10];

    /**
     * @see https://api-docs.deliveroo.com/v2.0/reference/get-orders-v2
     *
     * @throws ConnectionException
     */
    public function getOrders(
        string $brandId,
        string $restaurantId,
        ?string $cursor = null,
        ?Carbon $startDate = null,
        ?Carbon $endDate = null
    ): object {
        $request = $this->request();

        if (! empty($cursor)) {
            $request->withQueryParameters([
                'cursor' => $cursor,
            ]);
        }

        if (! empty($startDate)) {
            $request->withQueryParameters([
                'start_date' => $startDate->toIso8601String(),
            ]);
        }

        if (! empty($endDate)) {
            $request->withQueryParameters([
                'end_date' => $endDate->toIso8601String(),
            ]);
        }

        $response = $request->get("/order/v2/brand/{$brandId}/restaurant/{$restaurantId}/orders");

        if ($response->successful()) {
            $response = $response->json();

            $results = [
                'next' => $response['next'],
            ];

            $results['orders'] = collect($response['orders'])->map(function ($item) {
                return (object) $item;
            });

            return (object) $results;
        }

        $response->throw();
    }

    /**
     * @see https://api-docs.deliveroo.com/v2.0/reference/get-order-v2
     *
     * @throws ConnectionException
     */
    public function getOrder(string $orderId): object
    {
        $response = $this->request()->get("/order/v2/orders/{$orderId}");

        if ($response->successful()) {
            return $response->object();
        }

        $response->throw();
    }

    /**
     * @see https://api-docs.deliveroo.com/v2.0/reference/create-sync-status-1
     *
     * @throws ConnectionException
     * @throws Exception
     */
    public function syncStatusOrder(
        string $orderId,
        OrderSyncStatus $syncStatus,
        Carbon $occurredAt,
        ?Reason $reason = null,
        ?string $notes = null,
    ): Response {
        if (
            $syncStatus === OrderSyncStatus::FAILED
            && $reason === null
        ) {
            throw new Exception('Reason is required when sync status is failed');
        }

        $data = [
            'status' => $syncStatus->value,
            'occurred_at' => $occurredAt->toIso8601String(),
            'reason' => $reason,
            'notes' => $notes,
        ];

        return $this->request()->post("/order/v1/orders/{$orderId}/sync_status", $data);
    }

    /**
     * @see https://api-docs.deliveroo.com/v2.0/reference/patch-order-1
     *
     * @throws ConnectionException
     */
    public function updateOrderStatus(
        string $orderId,
        OrderStatus $status,
        ?OrderRejectReason $rejectReason = null,
        ?string $notes = null,
    ): Response {
        if (
            $status === OrderStatus::REJECTED
            && empty($rejectReason)
        ) {
            throw new Exception('Reject reason is required when status is rejected.');
        }

        $data = [
            'status' => $status,
            'reject_reason' => $rejectReason,
            'notes' => $notes,
        ];

        return $this->request()->patch("/order/v1/orders/{$orderId}", $data);
    }

    /**
     * @see https://api-docs.deliveroo.com/v2.0/reference/create-prep-stage-1
     *
     * @throws ConnectionException
     */
    public function setOrderPreparationStage(
        string $orderId,
        OrderStage $stage,
        Carbon $occurredAt,
        int $delay = null,
    ): Response {
        if (
            $delay !== null
            && ! in_array($delay, self::ORDER_DELAY_OPTIONS)
        ) {
            throw new Exception('Invalid delay value. Value must be either 0, 2, 4, 6, 8 or 10.');
        }

        $data = [
            'stage' => $stage->value,
            'occurred_at' => $occurredAt->toIso8601String(),
            'delay' => $delay,
        ];

        return $this->request()->post("/order/v1/orders/{$orderId}/prep_stage", $data);
    }
}
