<?php

namespace App\Http\Controllers;

use App\Models\PaymentMethod;
use App\Services\PaymentMethodService;
use App\Http\Resources\PaymentMethodResource;
use App\Http\Resources\PaymentMethodResources;
use App\Http\Requests\PaymentMethod\ShowPaymentMethodRequest;
use App\Http\Requests\PaymentMethod\ShowPaymentMethodsRequest;
use App\Http\Requests\PaymentMethod\CreatePaymentMethodRequest;
use App\Http\Requests\PaymentMethod\UpdatePaymentMethodRequest;
use App\Http\Requests\PaymentMethod\DeletePaymentMethodRequest;
use App\Http\Requests\PaymentMethod\DeletePaymentMethodsRequest;

class PaymentMethodController extends Controller
{
    /**
     * @var PaymentMethodService
     */
    protected $service;

    /**
     * PaymentMethodController constructor.
     *
     * @param PaymentMethodService $service
     */
    public function __construct(PaymentMethodService $service)
    {
        $this->service = $service;
    }

    /**
     * Show payment methods.
     *
     * @param ShowPaymentMethodsRequest $request
     * @return PaymentMethodResources|array
     */
    public function showPaymentMethods(ShowPaymentMethodsRequest $request): PaymentMethodResources|array
    {
        return $this->service->showPaymentMethods($request->validated());
    }

    /**
     * Create payment method.
     *
     * @param CreatePaymentMethodRequest $request
     * @return array
     */
    public function createPaymentMethod(CreatePaymentMethodRequest $request): array
    {
        return $this->service->createPaymentMethod($request->validated());
    }

    /**
     * Delete multiple payment methods.
     *
     * @param DeletePaymentMethodsRequest $request
     * @return array
     */
    public function deletePaymentMethods(DeletePaymentMethodsRequest $request): array
    {
        $paymentMethodIds = request()->input('payment_method_ids', []);
        return $this->service->deletePaymentMethods($paymentMethodIds);
    }

    /**
     * Show single payment method.
     *
     * @param ShowPaymentMethodRequest $request
     * @param PaymentMethod $paymentMethod
     * @return PaymentMethodResource
     */
    public function showPaymentMethod(ShowPaymentMethodRequest $request, PaymentMethod $paymentMethod): PaymentMethodResource
    {
        return $this->service->showPaymentMethod($paymentMethod);
    }

    /**
     * Update payment method.
     *
     * @param UpdatePaymentMethodRequest $request
     * @param PaymentMethod $paymentMethod
     * @return array
     */
    public function updatePaymentMethod(UpdatePaymentMethodRequest $request, PaymentMethod $paymentMethod): array
    {
        return $this->service->updatePaymentMethod($paymentMethod, $request->validated());
    }

    /**
     * Delete payment method.
     *
     * @param DeletePaymentMethodRequest $request
     * @param PaymentMethod $paymentMethod
     * @return array
     */
    public function deletePaymentMethod(DeletePaymentMethodRequest $request, PaymentMethod $paymentMethod): array
    {
        return $this->service->deletePaymentMethod($paymentMethod);
    }
}
