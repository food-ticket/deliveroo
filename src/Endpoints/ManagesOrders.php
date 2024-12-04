<?php

declare(strict_types=1);

namespace Foodticket\Deliveroo\Endpoints;

use App\Enums\OrderStatus;
use App\Models\Order;
use Carbon\Carbon;
use Exception;
use Foodticket\Deliveroo\Enums\OrderRejectReason;
use Foodticket\Deliveroo\Enums\OrderStage;
use Foodticket\Deliveroo\Enums\OrderSyncStatus;
use Foodticket\Deliveroo\Enums\Reason;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\Client\Response;

trait ManagesOrders
{
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

    public function getOrder(string $orderId): object
    {
        $response = $this->request()->get("/order/v2/orders/{$orderId}");

        if ($response->successful()) {
            return $response->object();
        }

        $response->throw();
    }

    /**
     * @throws RequestException
     * @throws ConnectionException
     */
    public function acceptOrder(Order $order): Order
    {
        // We always accept the order, and then update the status to pending.
        $response = $this->syncStatusOrder(
            orderId: $order->source_info,
            syncStatus: OrderSyncStatus::SUCCEEDED,
            occurredAt: Carbon::now(),
        );

        if (! $response->successful()) {
            $response->throw();
        }

        $order->status = OrderStatus::PENDING;

        return $order;
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

        return $this->request()
            ->post("/order/v1/orders/{$orderId}/sync_status", $data);
    }

    /**
     * @throws ConnectionException
     */
    public function updateOrderStatus(
        string $orderId,
        string $status,
        OrderRejectReason $rejectReason,
        ?string $notes = null,
    ): Response {
        $data = [
            'status' => $status,
            'reject_reason' => $rejectReason,
            'notes' => $notes,
        ];

        return $this->request()
            ->post("/order/v1/orders/{$orderId}", $data);
    }

    /**
     * @see https://api-docs.deliveroo.com/v2.0/reference/patch-order-1
     *
     * @throws ConnectionException
     */
    public function preparationState(
        string $orderId,
        OrderStage $orderStage,
        Carbon $occurredAt,
        ?int $delay = null,
    ): Response {
        $data = [
            'stage' => $orderStage->value,
            'occurred_at' => $occurredAt->toIso8601String(),
            'delay' => $delay,
        ];

        return $this->request()
            ->post("/order/v1/orders/{$orderId}/prep_stage", $data);
    }
}
