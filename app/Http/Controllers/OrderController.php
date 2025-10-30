<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Transaction;
use App\Services\OrderService;
use App\Http\Resources\OrderResource;
use App\Http\Resources\OrderResources;
use App\Http\Resources\TransactionResource;
use App\Http\Requests\Order\PayOrderRequest;
use App\Http\Requests\Order\ShowOrderRequest;
use App\Http\Requests\Order\ShowOrdersRequest;
use App\Http\Requests\Order\CreateOrderRequest;
use App\Http\Requests\Order\UpdateOrderRequest;
use App\Http\Requests\Order\DeleteOrderRequest;
use App\Http\Requests\Order\DeleteOrdersRequest;
use App\Http\Requests\Order\UpdateOrdersRequest;
use App\Http\Requests\Order\DownloadOrdersRequest;
use Symfony\Component\HttpFoundation\StreamedResponse;
use App\Http\Requests\Order\VerifyOrderPaymentRequest;
use App\Http\Requests\Order\ShowOrderStatusCountsRequest;
use \Symfony\Component\HttpFoundation\BinaryFileResponse;

class OrderController extends Controller
{
    /**
     * @var OrderService
     */
    protected $service;

    /**
     * OrderController constructor.
     *
     * @param OrderService $service
     */
    public function __construct(OrderService $service)
    {
        $this->service = $service;
    }

    /**
     * Show orders.
     *
     * @param ShowOrdersRequest $request
     * @return OrderResources|BinaryFileResponse|array
     */
    public function showOrders(ShowOrdersRequest $request): OrderResources|BinaryFileResponse|array
    {
        return $this->service->showOrders($request->validated());
    }

    /**
     * Create order.
     *
     * @param CreateOrderRequest $request
     * @return array
     */
    public function createOrder(CreateOrderRequest $request): array
    {
        return $this->service->createOrder($request->validated());
    }

    /**
     * Update orders.
     *
     * @param UpdateOrdersRequest $request
     * @return array
     */
    public function updateOrders(UpdateOrdersRequest $request): array
    {
        return $this->service->updateOrders($request->validated());
    }

    /**
     * Delete multiple orders.
     *
     * @param DeleteOrdersRequest $request
     * @return array
     */
    public function deleteOrders(DeleteOrdersRequest $request): array
    {
        $orderIds = request()->input('order_ids', []);
        return $this->service->deleteOrders($orderIds);
    }

    /**
     * Download orders.
     *
     * @param DownloadOrdersRequest $request
     * @return StreamedResponse
     */
    public function downloadOrders(DownloadOrdersRequest $request): StreamedResponse
    {
        return $this->service->downloadOrders($request->validated());
    }

    /**
     * Show order status counts.
     *
     * @param ShowOrderStatusCountsRequest $request
     * @return array
     */
    public function showOrderStatusCounts(ShowOrderStatusCountsRequest $request): array
    {
        return $this->service->showOrderStatusCounts($request->validated());
    }

    /**
     * Verify domain payment.
     *
     * @param VerifyOrderPaymentRequest $request
     * @param Transaction $transaction
     * @return TransactionResource
     */
    public function verifyOrderPayment(VerifyOrderPaymentRequest $request, Transaction $transaction): TransactionResource
    {
        return $this->service->verifyOrderPayment($transaction);
    }

    /**
     * Show order.
     *
     * @param ShowOrderRequest $request
     * @param Order $order
     * @return OrderResource
     */
    public function showOrder(ShowOrderRequest $request, Order $order): OrderResource
    {
        return $this->service->showOrder($order);
    }

    /**
     * Update order.
     *
     * @param UpdateOrderRequest $request
     * @param Order $order
     * @return array
     */
    public function updateOrder(UpdateOrderRequest $request, Order $order): array
    {
        return $this->service->updateOrder($order, $request->validated());
    }

    /**
     * Delete order.
     *
     * @param DeleteOrderRequest $request
     * @param Order $order
     * @return array
     */
    public function deleteOrder(DeleteOrderRequest $request, Order $order): array
    {
        return $this->service->deleteOrder($order);
    }

    /**
     * Pay order.
     *
     * @param PayOrderRequest $request
     * @param Order $order
     * @return array
     */
    public function payOrder(PayOrderRequest $request, Order $order): array
    {
        return $this->service->payOrder($order, $request->validated());
    }
}
