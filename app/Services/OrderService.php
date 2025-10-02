<?php

namespace App\Services;

use Exception;
use App\Models\Order;
use App\Models\Store;
use App\Jobs\SendSms;
use App\Models\Address;
use App\Models\Customer;
use App\Enums\Association;
use App\Enums\OrderStatus;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use App\Models\Transaction;
use App\Models\OrderProduct;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\DeliveryAddress;
use App\Enums\OrderPaymentStatus;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Http\Resources\OrderResource;
use App\Services\ShoppingCartService;
use App\Http\Resources\OrderResources;
use Illuminate\Database\Eloquent\Collection;
use Symfony\Component\HttpFoundation\StreamedResponse;
use \Symfony\Component\HttpFoundation\BinaryFileResponse;

class OrderService extends BaseService
{
    /**
     * Show orders.
     *
     * @param array $data
     * @return OrderResources|BinaryFileResponse|array
     */
    public function showOrders(array $data): OrderResources|BinaryFileResponse|array
    {
        $storeId = $data['store_id'] ?? null;
        $customerId = $data['customer_id'] ?? null;
        $placedByUserId = $data['placed_by_user_id'] ?? null;
        $createdByUserId = $data['created_by_user_id'] ?? null;
        $assignedToUserId = $data['assigned_to_user_id'] ?? null;
        $association = isset($data['association']) ? Association::tryFrom($data['association']) : null;

        if($association == Association::SUPER_ADMIN) {

            $query = Order::query();

        }else if($association == Association::TEAM_MEMBER) {

            $query = Order::whereHas('store.users', function ($query) {
                $query->where('store_user.user_id', Auth::user()->id);
            });

        }else if($customerId) {

            $query = Order::where('customer_id', $customerId);

        }else if($createdByUserId) {

            $query = Order::where('created_by_user_id', $createdByUserId);

        }else if($assignedToUserId) {

            $query = Order::where('assigned_to_user_id', $assignedToUserId);

        }else if($placedByUserId) {

            $query = Order::where('placed_by_user_id', $placedByUserId);

        }else{

            $query = Order::where('placed_by_user_id', Auth::user()->id);

        }

        if($storeId) {
            $query = $query->where('store_id', $storeId);
        }

        $query = $query->when(!request()->has('_sort'), fn($query) => $query->latest());
        return $this->setQuery($query)->getOutput();
    }

    /**
     * Show order status counts.
     *
     * @param array $data
     * @return array
     */
    public function showOrderStatusCounts(array $data): array
    {
        $storeId = $data['store_id'];
        $store = $storeId ? Store::find($storeId) : null;
        $placedByUserId = $data['placed_by_user_id'] ?? null;

        $query = DB::table('orders')->selectRaw('
            COUNT(*) as total_orders,
            CAST(SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) AS UNSIGNED) as waiting_count,
            CAST(SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) AS UNSIGNED) as cancelled_count,
            CAST(SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) AS UNSIGNED) as completed_count,
            CAST(SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) AS UNSIGNED) as on_its_way_count,
            CAST(SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) AS UNSIGNED) as ready_for_pickup_count,
            CAST(SUM(CASE WHEN payment_status = ? THEN 1 ELSE 0 END) AS UNSIGNED) as paid_count,
            CAST(SUM(CASE WHEN payment_status = ? THEN 1 ELSE 0 END) AS UNSIGNED) as unpaid_count,
            CAST(SUM(CASE WHEN payment_status = ? THEN 1 ELSE 0 END) AS UNSIGNED) as partially_paid_count,
            CAST(SUM(CASE WHEN payment_status = ? THEN 1 ELSE 0 END) AS UNSIGNED) as WAITING_CONFIRMATION_count
        ', [
            OrderStatus::WAITING->value,
            OrderStatus::CANCELLED->value,
            OrderStatus::COMPLETED->value,
            OrderStatus::ON_ITS_WAY->value,
            OrderStatus::READY_FOR_PICKUP->value,
            OrderPaymentStatus::PAID->value,
            OrderPaymentStatus::UNPAID->value,
            OrderPaymentStatus::PARTIALLY_PAID->value,
            OrderPaymentStatus::WAITING_CONFIRMATION->value,
        ]);

        if($store) $query->where('store_id', $storeId);
        if($placedByUserId) $query->where('placed_by_user_id', $placedByUserId);

        $result = $query->first();

        return [
            'total_orders' => $result->total_orders,
            'status_counts' => [
                'waiting' => $result->waiting_count,
                'completed' => $result->completed_count,
                'on_its_way' => $result->on_its_way_count,
                'ready_for_pickup' => $result->ready_for_pickup_count,
                'cancelled' => $result->cancelled_count,
            ],
            'payment_status_counts' => [
                'paid' => $result->paid_count,
                'unpaid' => $result->unpaid_count,
                'partially_paid' => $result->partially_paid_count,
                'WAITING_CONFIRMATION' => $result->WAITING_CONFIRMATION_count,
            ]
        ];

    }

    /**
     * Create order.
     *
     * @param array $data
     * @return array
     */
    public function createOrder(array $data): array
    {
        $storeId = $data['store_id'];
        $store = Store::find($storeId);
        $inspect = $data['inspect'] ?? false;

        $shoppingCartInstance = (new ShoppingCartService)->startInspection($store);

        if($inspect) {
            return $shoppingCartInstance->getShoppingCart();
        }else{
            $inspectedShoppingCart = $shoppingCartInstance->getShoppingCart(false);
        }

        $totalOrderProducts = $inspectedShoppingCart['totals_summary']['order_products']['total_products'];
        if($totalOrderProducts == 0) throw new Exception('The shopping cart does not have products to place an order');

        $customer = $this->hasCustomerFields($data) ? $this->updateOrCreateCustomer($store, $data) : null;
        $uncreatedOrder = (new Order)->setRelations(['store' => $store, 'customer' => $customer]);
        $orderPayload = $this->prepareOrderPayload($uncreatedOrder, $data, $inspectedShoppingCart);

        $order = Order::create($orderPayload);
        $orderProducts = $this->syncOrderProducts($order, $inspectedShoppingCart);
        $orderPromotions = $this->syncOrderPromotions($order, $inspectedShoppingCart);
        $orderDiscounts = $this->syncOrderDiscounts($order, $inspectedShoppingCart);
        $orderFees = $this->syncOrderFees($order, $inspectedShoppingCart);

        $this->addOrderComment($order, 'Order created');
        $order->setRelations([
            'store' => $store,
            'customer' => $customer,
            'orderFees' => $orderFees,
            'orderProducts' => $orderProducts,
            'orderDiscounts' => $orderDiscounts,
            'orderPromotions' => $orderPromotions
        ]);

        $this->updateCustomerStatistics($order);
        $deliveryAddress = $this->addDeliveryAddress($order, $data);
        if($customer && $deliveryAddress) $this->createCustomerAddress($customer, $deliveryAddress);

        $number = $this->generateOrderNumber($order);
        $summary = $this->generateOrderSummary($order);

        $order->update([
            'number' => $number,
            'summary' => $summary
        ]);

        //  $this->sendOrderCreatedNotifications($order);
        $shoppingCartInstance->forgetCache($store);

        //  THIS SHOULD BE HANDLED BY A WORKFLOW INSTEAD (Then the workflow can send to the specified mobile number)
        if($store->storeQuota->sms_credits && $store->ussd_mobile_number) {
            $messageCrafterService = new MessageCrafterService();
            $smsMessage = $messageCrafterService->craftNewOrderForSellerMessage($order);
            SendSms::dispatch($smsMessage, $store->ussd_mobile_number->formatE164(), $store->id);
        }

        if(!$this->checkIfHasRelationOnRequest('store')) $order->unsetRelation('store');
        if(!$this->checkIfHasRelationOnRequest('customer')) $order->unsetRelation('customer');
        if(!$this->checkIfHasRelationOnRequest('orderFees')) $order->unsetRelation('orderFees');
        if(!$this->checkIfHasRelationOnRequest('orderProducts')) $order->unsetRelation('orderProducts');
        if(!$this->checkIfHasRelationOnRequest('orderDiscounts')) $order->unsetRelation('orderDiscounts');
        if(!$this->checkIfHasRelationOnRequest('orderPromotions')) $order->unsetRelation('orderPromotions');

        return $this->showCreatedResource($order);
    }

    /**
     * Update orders.
     *
     * @param array $data
     * @return array
     */
    public function updateOrders(array $data): array
    {
        $storeId = $data['store_id'];
        $orderIds = $data['order_ids'];
        $totalOrders = count($orderIds);
        $fillableData = array_intersect_key($data, array_flip([
            'status', 'payment_status', 'assigned_to_user_id'
        ]));

        Order::whereIn('id', $orderIds)->where('store_id', $storeId)->update($fillableData);
        return ['updated' => true, 'message' => $totalOrders . ($totalOrders == 1 ? ' order': ' orders') . ' updated'];
    }

    /**
     * Delete Orders.
     *
     * @param array $orderIds
     * @return array
     * @throws Exception
     */
    public function deleteOrders(array $orderIds): array
    {
        $orders = Order::whereIn('id', $orderIds)->get();

        if ($totalOrders = $orders->count()) {

            foreach ($orders as $order) {

                $this->deleteOrder($order);

            }

            return ['message' => $totalOrders . ($totalOrders == 1 ? ' Order' : ' Orders') . ' deleted'];

        } else {
            throw new Exception('No Orders deleted');
        }
    }

    /**
     * Download orders.
     *
     * @param array $data
     * @return StreamedResponse
     */
    public function downloadOrders(array $data): StreamedResponse
    {
        $storeId = $data['store_id'];
        $orderIds = $data['order_ids'];

        $store = Store::with(['logo'])->find($storeId);
        $orders = Order::whereIn('id', $orderIds)->where('store_id', $storeId)->with(['orderProducts', 'orderPromotions'])->get();

        // Convert objects to arrays
        $store = json_decode(json_encode($store), true);
        $orders = $orders->map(function ($order) {
            return json_decode(json_encode($order), true);
        })->toArray();

        // Generate the PDF
        $pdf = Pdf::loadView('pdfs.order.show-invoice', compact('store', 'orders'));

        return response()->streamDownload(function () use ($pdf) {
            echo $pdf->stream();
        }, 'name.pdf');
    }

    /**
     * Show order.
     *
     * @param Order $order
     * @return OrderResource
     */
    public function showOrder(Order $order): OrderResource
    {
        return $this->showResource($order);
    }

    /**
     * Update order.
     *
     * @param Order $order
     * @param array $data
     * @return array
     */
    public function updateOrder(Order $order, array $data): array
    {
        if(isset($data['cart_products'])) {

            $store = $order->store;

            $shoppingCartInstance = (new ShoppingCartService)->startInspection($store);
            $inspectedShoppingCart = $shoppingCartInstance->getShoppingCart(false);

            $totalOrderProducts = $inspectedShoppingCart['totals_summary']['order_products']['total_products'];
            if($totalOrderProducts == 0) return ['updated' => false, 'message' => 'The shopping cart does not have products to update order'];

            $customer = $this->hasCustomerFields($data) ? $this->updateOrCreateCustomer($store, $data) : null;
            $unsavedOrder = $order->setRelations(['customer' => $customer]);
            $orderPayload = $this->prepareOrderPayload($unsavedOrder, $data, $inspectedShoppingCart);

            $oldOrder = clone $order;
            $order->update($orderPayload);
            $order = $this->updateOrderAmountBalance($order);

            $orderProducts = $this->syncOrderProducts($order, $inspectedShoppingCart, true);
            $orderPromotions = $this->syncOrderPromotions($order, $inspectedShoppingCart, true);
            $orderDiscounts = $this->syncOrderDiscounts($order, $inspectedShoppingCart, true);
            $orderFees = $this->syncOrderFees($order, $inspectedShoppingCart, true);

            $this->addOrderComment($order, 'Order updated');

            $order->setRelations([
                'store' => $store,
                'customer' => $customer,
                'orderFees' => $orderFees,
                'orderProducts' => $orderProducts,
                'orderDiscounts' => $orderDiscounts,
                'orderPromotions' => $orderPromotions
            ]);

            $this->updateCustomerStatistics($order, $oldOrder);
            $deliveryAddress = $this->addOrUpdateDeliveryAddress($order, $data);
            if($customer && $deliveryAddress) $this->createCustomerAddress($customer, $deliveryAddress);

            $summary = $this->generateOrderSummary($order);

            $order->update([
                'summary' => $summary
            ]);

            //  $this->sendOrderUpdatedNotifications($order);
            $shoppingCartInstance->forgetCache($store);

            if(!$this->checkIfHasRelationOnRequest('store')) $order->unsetRelation('store');
            if(!$this->checkIfHasRelationOnRequest('customer')) $order->unsetRelation('customer');
            if(!$this->checkIfHasRelationOnRequest('orderFees')) $order->unsetRelation('orderFees');
            if(!$this->checkIfHasRelationOnRequest('orderProducts')) $order->unsetRelation('orderProducts');
            if(!$this->checkIfHasRelationOnRequest('orderDiscounts')) $order->unsetRelation('orderDiscounts');
            if(!$this->checkIfHasRelationOnRequest('orderPromotions')) $order->unsetRelation('orderPromotions');
            if(!$this->checkIfHasRelationOnRequest('deliveryAddress')) $order->unsetRelation('deliveryAddress');

            return $this->showUpdatedResource($order);

        }else{

            $order->update($data);

        }

        return $this->showUpdatedResource($order);
    }

    /**
     * Delete order.
     *
     * @param Order $order
     * @return array
     * @throws Exception
     */
    public function deleteOrder(Order $order): array
    {
        $deleted = $order->delete();

        if ($deleted) {
            return ['message' => 'Order deleted'];
        } else {
            throw new Exception('Order delete unsuccessful');
        }
    }

    /**
     * Update or create customer.
     *
     * @param Store $store
     * @param array $data
     * @return Customer|null
     */
    public function updateOrCreateCustomer(Store $store, array $data): Customer|null
    {
        $customer = $this->findCustomer($store, $data);

        if($customer) {
            if($this->shouldUpdateCustomer($customer, $data)) $customer = $this->updateCustomer($customer, $data);
        }else{
            $customer = $this->createCustomer($store, $data);
        }

        return $customer;
    }

    /**
     * Should update customer.
     *
     * @param Customer $customer
     * @param array $data
     * @return bool
     */
    public function shouldUpdateCustomer(Customer $customer, array $data): bool
    {
        $fieldMap = $this->getCustomerUpdatableFields();

        $fieldsToUpdate = [];
        foreach ($fieldMap as $requestField => $modelField) {
            if (array_key_exists($requestField, $data)) {
                $fieldsToUpdate[$modelField] = $data[$requestField];
            }
        }

        if (empty($fieldsToUpdate)) {
            return false;
        }

        $currentData = $customer->only(array_values($fieldMap));

        foreach ($fieldsToUpdate as $modelField => $newValue) {
            $currentValue = $currentData[$modelField] ?? null;
            if ($newValue !== $currentValue) {
                return true;
            }
        }

        return false;
    }

    /**
     * Find customer.
     *
     * @param Store $store
     * @param array $data
     * @return Customer|null
     */
    public function findCustomer(Store $store, array $data): Customer|null
    {
        $hasEmail = isset($data['customer_email']);
        $hasMobileNumber = isset($data['customer_mobile_number']);
        if($hasMobileNumber) return $store->customers()->searchMobileNumber($data['customer_mobile_number'])->first();
        if($hasEmail) return $store->customers()->searchEmail($data['customer_email'])->first();
        return null;
    }

    /**
     * Update customer.
     *
     * @param Customer $customer
     * @param array $data
     * @return Customer
     */
    public function updateCustomer(Customer $customer, array $data): Customer
    {
        $fieldMap = $this->getCustomerUpdatableFields();

        $fieldsToUpdate = [];
        foreach ($fieldMap as $requestField => $modelField) {
            if (array_key_exists($requestField, $data)) {
                $fieldsToUpdate[$modelField] = $data[$requestField];
            }
        }

        if (!empty($fieldsToUpdate)) {
            tap($customer)->update($fieldsToUpdate);
        }

        return $customer;
    }

    /**
     * Create customer.
     *
     * @param Store $store
     * @param array $data
     * @return Customer|null
     */
    public function createCustomer(Store $store, array $data): Customer|null
    {
        $fieldMap = $this->getCustomerUpdatableFields();

        $customerData = [];
        foreach ($fieldMap as $requestField => $modelField) {
            if (array_key_exists($requestField, $data)) {
                $customerData[$modelField] = $data[$requestField];
            }
        }

        $hasEmail = array_key_exists('email', $customerData);
        $hasMobileNumber = array_key_exists('mobile_number', $customerData);

        if ($hasEmail || $hasMobileNumber) {
            $customerData['currency'] = $store->currency;
            return $store->customers()->create($customerData);
        }

        return null;
    }

    /**
     * Check if has customer fields.
     *
     * @param array $data
     * @return bool
     */
    public function hasCustomerFields(array $data): bool
    {
        $customerFields = ['customer_first_name', 'customer_last_name', 'customer_mobile_number', 'customer_email'];
        return Arr::hasAny($data, $customerFields);
    }

    /**
     * Get customer updatable fields.
     *
     * @return array
     */
    public function getCustomerUpdatableFields(): array
    {
        return [
            'customer_mobile_number' => 'mobile_number',
            'customer_first_name' => 'first_name',
            'customer_last_name' => 'last_name',
            'customer_birthday' => 'birthday',
            'customer_email' => 'email'
        ];
    }

    /**
     * Update customer statistics.
     *
     * @param Order $order
     * @param Order|null $oldOrder
     * @return void
     */
    private function updateCustomerStatistics(Order $order, Order|null $oldOrder = null): void
    {
        $updateCustomerStatistics = function($order, $increment = true) {

            $customer = $order->customer;

            if($customer) {

                if(!$increment) $customer->refresh();
                $totalOrders = $customer->total_orders + ($increment ? 1 : -1);
                $totalSpend = $customer->total_spend->amount + ($increment ? $order->grand_total->amount : -$order->grand_total->amount);
                $totalAverageSpend = $totalOrders == 0 ? 0 : ($totalSpend / $totalOrders);

                $customer->update([
                    'last_order_at' => now(),
                    'total_spend' => $totalSpend,
                    'total_orders' => $totalOrders,
                    'total_average_spend' => $totalAverageSpend
                ]);

            }

        };

        $updateCustomerStatistics($order);
        if($oldOrder) $updateCustomerStatistics($oldOrder, false);
    }

    /**
     * Create customer address.
     *
     * @param Customer $customer
     * @param DeliveryAddress $deliveryAddress
     * @return void
     */
    private function createCustomerAddress(Customer $customer, DeliveryAddress $deliveryAddress): void
    {
        if(!$customer->address()->exists()) {
            Address::create(array_merge($deliveryAddress->getAttributes(), [
                'owner_id' => $customer->id,
                'owner_type' => 'customer'
            ]));
        }
    }

    /**
     * Prepare order payload.
     *
     * @param Order $order
     * @param array $data
     * @param array $inspectedShoppingCart
     * @return array
     */
    private function prepareOrderPayload(Order $order, array $data, array $inspectedShoppingCart): array
    {
        $store = $order->store;
        $customer = $order->customer;
        $isc = $inspectedShoppingCart;
        $unsavedOrder = $order->id != null;
        $uncreatedOrder = $order->id == null;
        $customerFirstName = $customerLastName = $customerMobileNumber = $customerEmail = null;

        if($customer) {

            $customerEmail = $customer->email;
            $customerLastName = $customer->last_name;
            $customerFirstName = $customer->first_name;
            $customerMobileNumber = $customer->mobile_number?->formatE164();

        }else if(isset($data['customer'])) {

            $customerEmail = $data['customer_email'] ?? null;
            $customerLastName = $data['customer_last_name'] ?? null;
            $customerFirstName = $data['customer_first_name'] ?? null;
            $customerMobileNumber = $data['customer_mobile_number'] ?? null;

        }

        $association = isset($data['association']) ? Association::tryFrom($data['association']) : null;
        $isTeamMember = $association == Association::TEAM_MEMBER;

        $createdByUserId = ($uncreatedOrder && $isTeamMember) ? Auth::user()->id : $order->created_by_user_id;
        $placedByUserId = $createdByUserId ? $createdByUserId : ($order->placed_by_user_id ?? Auth::user()?->id);

        $deliveryMethodOption = collect($isc['delivery_method_options'])->firstWhere(fn($deliveryMethodOption) => $deliveryMethodOption['is_selected']);

        $orderPayload = [
            'summary' => null,
            'currency' => $store->currency,
            'status' => OrderStatus::WAITING->value,
            'subtotal' => $isc['totals']['subtotal']->amount,
            'discount_total' => $isc['totals']['discount_total']->amount,
            'subtotal_after_discount' => $isc['totals']['subtotal_after_discount']->amount,
            'vat_method' => $isc['totals']['vat']['method'],
            'vat_rate' => $isc['totals']['vat']['rate']['value'],
            'vat_amount' => $isc['totals']['vat']['amount']->amount,
            'fee_total' => $isc['totals']['fee_total']->amount,
            'adjustment_total' => $isc['totals']['adjustment_total']->amount,
            'grand_total' => $isc['totals']['grand_total']->amount,

            'payment_status' => OrderPaymentStatus::UNPAID->value,
            'paid_total' => 0,
            'paid_percentage' => 0,
            'outstanding_total' => $isc['totals']['grand_total']->amount,
            'outstanding_percentage' => 100,

            'total_products' => $isc['totals_summary']['order_products']['total_products'],
            'total_cancelled_products' => $isc['totals_summary']['order_products']['total_cancelled_products'],
            'total_uncancelled_products' => $isc['totals_summary']['order_products']['total_uncancelled_products'],
            'total_product_quantities' => $isc['totals_summary']['order_products']['total_product_quantities'],
            'total_cancelled_product_quantities' => $isc['totals_summary']['order_products']['total_cancelled_product_quantities'],
            'total_uncancelled_product_quantities' => $isc['totals_summary']['order_products']['total_uncancelled_product_quantities'],

            'total_promotions' => $isc['totals_summary']['order_promotions']['total_promotions'],
            'total_cancelled_promotions' => $isc['totals_summary']['order_promotions']['total_cancelled_promotions'],
            'total_uncancelled_promotions' => $isc['totals_summary']['order_promotions']['total_uncancelled_promotions'],
            'applied_promotion_code' => isset($isc['promotion_code']['applied']) && $isc['promotion_code']['applied'] == true,

            'delivery_method_id' =>  is_null($deliveryMethodOption) ? null : $deliveryMethodOption['id'],
            'delivery_method_name' =>  is_null($deliveryMethodOption) ? null : $deliveryMethodOption['name'],

            'delivery_distance_value' => is_null($deliveryMethodOption) || is_null($deliveryMethodOption['distance']) ? null :$deliveryMethodOption['distance']['value'],
            'delivery_distance_unit' => is_null($deliveryMethodOption) || is_null($deliveryMethodOption['distance']) ? null : $deliveryMethodOption['distance']['unit'],
            'delivery_distance_text' => is_null($deliveryMethodOption) || is_null($deliveryMethodOption['distance']) ? null : $deliveryMethodOption['distance']['text'],

            'delivery_duration_value' => is_null($deliveryMethodOption) || is_null($deliveryMethodOption['duration']) ? null :$deliveryMethodOption['duration']['value'],
            'delivery_duration_text' => is_null($deliveryMethodOption) || is_null($deliveryMethodOption['duration']) ? null :$deliveryMethodOption['duration']['text'],

            'delivery_weight_value' => is_null($deliveryMethodOption) || is_null($deliveryMethodOption['weight']) ? null :$deliveryMethodOption['weight']['value'],
            'delivery_weight_unit' => is_null($deliveryMethodOption) || is_null($deliveryMethodOption['weight']) ? null :$deliveryMethodOption['weight']['unit'],
            'delivery_weight_text' => is_null($deliveryMethodOption) || is_null($deliveryMethodOption['weight']) ? null :$deliveryMethodOption['weight']['text'],

            'free_delivery' => $isc['free_delivery'],

            'delivery_date' =>  is_null($deliveryMethodOption) ? null : $deliveryMethodOption['date'],
            'delivery_timeslot' =>  is_null($deliveryMethodOption) ? null : $deliveryMethodOption['timeslot'],

            'collection_code' => null,

            'customer_id' => $customer?->id,
            'customer_email' => $customerEmail,
            'customer_last_name' => $customerLastName,
            'customer_first_name' => $customerFirstName,
            'customer_mobile_number' => $customerMobileNumber,
            'customer_note' => $data['customer_note'] ?? null,

            'placed_by_user_id' => $placedByUserId,
            'created_by_user_id' => $createdByUserId,

            'store_id' => $store->id
        ];

        if($isTeamMember) {

            $orderPayload = array_merge($orderPayload, [
                'last_viewed_by_team_at' => now(),
                'total_views_by_team' => ($order?->total_views_by_team ?? 0) + 1,
                'first_viewed_by_team_at' => $order?->first_viewed_by_team_at ?? now(),

                'remark' => array_key_exists('remark', $data) ? $data['remark'] : $order?->remark,
                'status' => array_key_exists('status', $data) ? $data['status'] : $order?->status,
                'courier_id' => array_key_exists('courier_id', $data) ? $data['courier_id'] : $order?->courier_id,
                'internal_note' => array_key_exists('internal_note', $data) ? $data['internal_note'] : $order?->internal_note,
                'payment_status' => array_key_exists('payment_status', $data) ? $data['payment_status'] : $order?->payment_status,
                'collection_note' => array_key_exists('collection_note', $data) ? $data['collection_note'] : $order?->collection_note,
                'tracking_number' => array_key_exists('tracking_number', $data) ? $data['tracking_number'] : $order?->tracking_number,
                'assigned_to_user_id' => array_key_exists('assigned_to_user_id', $data) ? $data['assigned_to_user_id'] : $order?->assigned_to_user_id,

                'cancellation_reason' => array_key_exists('cancellation_reason', $data) ? $data['cancellation_reason'] : $order?->cancellation_reason,
                'cancelled_at' => array_key_exists('status', $data) && $data['status'] == OrderStatus::CANCELLED->value ? now() : $order?->cancelled_at,
                'other_cancellation_reason' => array_key_exists('other_cancellation_reason', $data) ? $data['other_cancellation_reason'] : $order?->other_cancellation_reason,
            ]);

        }

        return $orderPayload;
    }

    /**
     * Sync order products.
     *
     * @param Order $order
     * @param array $inspectedShoppingCart
     * @param array $deleteExisting
     * @return Collection
     */
    public function syncOrderProducts(Order $order, array $inspectedShoppingCart, bool $deleteExisting = false): Collection
    {
        $inserts = collect($inspectedShoppingCart['order_products'])->map(function($orderProduct) use ($order) {
            if(empty($orderProduct['detected_changes'])) $orderProduct['detected_changes'] = null;
            $orderProduct['store_id'] = $order->store_id;
            $orderProduct['order_id'] = $order->id;
            return $orderProduct;
        });

        if($deleteExisting) $order->orderProducts()->delete();
        return $order->orderProducts()->createMany($inserts);
    }

    /**
     * Sync order promotions.
     *
     * @param Order $order
     * @param array $inspectedShoppingCart
     * @param array $deleteExisting
     * @return Collection
     */
    public function syncOrderPromotions(Order $order, array $inspectedShoppingCart, bool $deleteExisting = false): Collection
    {
        $inserts = collect($inspectedShoppingCart['order_promotions'])->map(function($orderPromotion) use ($order) {
            if(empty($orderPromotion['detected_changes'])) $orderPromotion['detected_changes'] = null;
            $orderPromotion['store_id'] = $order->store_id;
            $orderPromotion['order_id'] = $order->id;
            return $orderPromotion;
        });

        if($deleteExisting) $order->orderPromotions()->delete();
        return $order->orderPromotions()->createMany($inserts);
    }

    /**
     * Sync order discounts.
     *
     * @param Order $order
     * @param array $inspectedShoppingCart
     * @param array $deleteExisting
     * @return Collection
     */
    public function syncOrderDiscounts(Order $order, array $inspectedShoppingCart, bool $deleteExisting = false): Collection
    {
        $inserts = collect($inspectedShoppingCart['totals']['discounts'])->map(function($orderDiscount) use ($order) {
            $orderDiscount['store_id'] = $order->store_id;
            $orderDiscount['order_id'] = $order->id;
            return $orderDiscount;
        });

        if($deleteExisting) $order->orderDiscounts()->delete();
        return $order->orderDiscounts()->createMany($inserts);
    }

    /**
     * Sync order fees.
     *
     * @param Order $order
     * @param array $inspectedShoppingCart
     * @param array $deleteExisting
     * @return Collection
     */
    public function syncOrderFees(Order $order, array $inspectedShoppingCart, bool $deleteExisting = false): Collection
    {
        $inserts = collect($inspectedShoppingCart['totals']['fees'])->map(function($orderFee) use ($order) {
            $orderFee['store_id'] = $order->store_id;
            $orderFee['order_id'] = $order->id;
            return $orderFee;
        });

        if($deleteExisting) $order->orderFees()->delete();
        return $order->orderFees()->createMany($inserts);
    }


    /**
     * Add order history comment.
     *
     * @param Order $order
     * @param string $comment
     * @return void
     */
    public function addOrderComment(Order $order, string $comment): void
    {
        $order->orderComments()->create([
            'comment' => $comment,
            'store_id' => $order->store_id
        ]);
    }

    /**
     * Add delivery address.
     *
     * @return DeliveryAddress|null
     */
    private function addDeliveryAddress($order, array $data): DeliveryAddress|null
    {
        $deliveryAddressPayload = $this->prepareDeliveryAddressPayload($order, $data);
        return $deliveryAddressPayload ? DeliveryAddress::create($deliveryAddressPayload) : null;
    }

    /**
     * Add or update delivery address.
     *
     * @return DeliveryAddress|null
     */
    private function addOrUpdateDeliveryAddress($order, array $data): DeliveryAddress|null
    {
        $deliveryAddressPayload = $this->prepareDeliveryAddressPayload($order, $data);
        if($deliveryAddressPayload) {
            $oldDeliveryAddress = $order->deliveryAddress;
            if($oldDeliveryAddress) {
                $oldDeliveryAddress->update($deliveryAddressPayload);
                return $oldDeliveryAddress;
            }else{
                return DeliveryAddress::create($deliveryAddressPayload);
            }
        }else{
            return null;
        }
    }

    /**
     * Prepate delivery address payload.
     *
     * @param Order $order
     * @param array $data
     * @return array|null
     */
    private function prepareDeliveryAddressPayload($order, array $data): array|null
    {
        if(isset($data['address_id'])) {
            $address = Address::find($data['address_id']);
            return $address ? array_merge($address->getAttributes(), ['order_id' => $order->id]) : null;
        }else if(isset($data['delivery_address'])) {

            $data = $data['delivery_address'];

            return [
                'order_id' => $order->id,
                'type' => $data['type'] ?? null,
                'city' => $data['city'] ?? null,
                'state' => $data['state'] ?? null,
                'country' => $data['country'] ?? null,
                'address_line' => $data['address_line'],
                'latitude' => $data['latitude'] ?? null,
                'place_id' => $data['place_id'] ?? null,
                'longitude' => $data['longitude'] ?? null,
                'description' => $data['description'] ?? null,
                'postal_code' => $data['postal_code'] ?? null,
                'address_line2' => $data['address_line2'] ?? null
            ];
        }

        return null;
    }

    /**
     * Generate order number.
     *
     * @param Order $order
     * @return string
     */
    private function generateOrderNumber(Order $order): string
    {
        if($order->number) return $order->number;

        // Use transaction with locking to ensure unique sequential number
        return DB::transaction(function () use ($order) {

            // Lock the store row to prevent concurrent updates
            $store = $order->store->lockForUpdate()->first();

            // Generate order number
            $prefix = $store->order_number_prefix ?? '';
            $suffix = $store->order_number_suffix ?? '';

            // Increment sequence
            $sequence = $store->order_number_counter + 1;
            $store->update(['order_number_counter' => $sequence]);

            // Pad to 4 digits (e.g., 0001)
            $paddedSequence = Str::padLeft($sequence, ($store->order_number_padding + 1), '0');
            return "{$prefix}{$paddedSequence}{$suffix}";

        });
    }

    /**
     * Generate order summary.
     *
     * @param Order $order
     * @return string
     */
    private function generateOrderSummary(Order $order): string
    {
        $summary = collect($order->orderProducts)->sortBy('position')->map(function(OrderProduct $orderProduct) {
            return $orderProduct->quantity >= 2 ? $orderProduct->quantity . 'x(' . $orderProduct->name . ')' : $orderProduct->name;
        })->join(', ', ' and ');

        $summary .= ' for ' . $order->grand_total->amount_with_currency;

        if($order->discount_total->amount > 0) {
            $summary .= ' while saving ' . $order->discount_total->amount_with_currency;
        }

        if($order->free_delivery) {
            $summary .= ' plus free delivery';
        }

        return $summary;
    }

    /**
     * Update order amount balance.
     *
     * @param Order $order
     * @return Order
     */
    public function updateOrderAmountBalance(Order $order): Order
    {
        $transactions = $order->transactions()->get();
        $grandTotal = $order->grand_total->amount;

        //  Calculate the order balance paid
        $paidTotal = collect($transactions)->filter(fn(Transaction $transaction) => $transaction->isPaid())->map(fn(Transaction $transaction) => $transaction->amount->amount)->sum();
        $paidPercentage = (int) ($grandTotal > 0 ? ($paidTotal / $grandTotal * 100) : 0);

        //  Calculate the order balance outstanding payment
        $outstandingTotal = $grandTotal - $paidTotal < 0 ? 0 : $grandTotal - $paidTotal;
        $outstandingPercentage = (int) ($grandTotal > 0 ? ($outstandingTotal / $grandTotal * 100) : 0);

        if( $paidPercentage == 0 ) {
            $paymentStatus = OrderPaymentStatus::UNPAID;
        }elseif( $paidPercentage == 100 ) {
            $paymentStatus = OrderPaymentStatus::PAID;
        }else {
            $paymentStatus = OrderPaymentStatus::PARTIALLY_PAID;
        }

        $order->update([
            'payment_status' => $paymentStatus->value,

            'paid_total' => $paidTotal,
            'paid_percentage' => $paidPercentage,

            'outstanding_total' => $outstandingTotal,
            'outstanding_percentage' => $outstandingPercentage,
        ]);

        return $order;
    }
}
