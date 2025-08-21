<?php

namespace App\Http\Controllers;

use App\Models\PricingPlan;
use App\Models\Transaction;
use App\Services\PricingPlanService;
use App\Http\Resources\TransactionResource;
use App\Http\Resources\PricingPlanResource;
use App\Http\Resources\PricingPlanResources;
use App\Http\Requests\PricingPlan\PayPricingPlanRequest;
use App\Http\Requests\PricingPlan\ShowPricingPlanRequest;
use App\Http\Requests\PricingPlan\ShowPricingPlansRequest;
use App\Http\Requests\PricingPlan\CreatePricingPlanRequest;
use App\Http\Requests\PricingPlan\UpdatePricingPlanRequest;
use App\Http\Requests\PricingPlan\DeletePricingPlanRequest;
use App\Http\Requests\PricingPlan\DeletePricingPlansRequest;
use App\Http\Requests\PricingPlan\VerifyPricingPlanPaymentRequest;

class PricingPlanController extends Controller
{
    /**
     * @var PricingPlanService
     */
    protected $service;

    /**
     * PricingPlanController constructor.
     *
     * @param PricingPlanService $service
     */
    public function __construct(PricingPlanService $service)
    {
        $this->service = $service;
    }

    /**
     * Show pricing plans.
     *
     * @param ShowPricingPlansRequest $request
     * @return PricingPlanResources|array
     */
    public function showPricingPlans(ShowPricingPlansRequest $request): PricingPlanResources|array
    {
        return $this->service->showPricingPlans($request->validated());
    }

    /**
     * Create pricing plan.
     *
     * @param CreatePricingPlanRequest $request
     * @return array
     */
    public function createPricingPlan(CreatePricingPlanRequest $request): array
    {
        return $this->service->createPricingPlan($request->validated());
    }

    /**
     * Delete multiple pricing plans.
     *
     * @param DeletePricingPlansRequest $request
     * @return array
     */
    public function deletePricingPlans(DeletePricingPlansRequest $request): array
    {
        $pricingPlanIds = request()->input('pricing_plan_ids', []);
        return $this->service->deletePricingPlans($pricingPlanIds);
    }

    /**
     * Show pricing plan.
     *
     * @param ShowPricingPlanRequest $request
     * @param PricingPlan $pricingPlan
     * @return PricingPlanResource
     */
    public function showPricingPlan(ShowPricingPlanRequest $request, PricingPlan $pricingPlan): PricingPlanResource
    {
        return $this->service->showPricingPlan($pricingPlan);
    }

    /**
     * Update pricing plan.
     *
     * @param UpdatePricingPlanRequest $request
     * @param PricingPlan $pricingPlan
     * @return array
     */
    public function updatePricingPlan(UpdatePricingPlanRequest $request, PricingPlan $pricingPlan): array
    {
        return $this->service->updatePricingPlan($pricingPlan, $request->validated());
    }

    /**
     * Delete pricing plan.
     *
     * @param DeletePricingPlanRequest $request
     * @param PricingPlan $pricingPlan
     * @return array
     */
    public function deletePricingPlan(DeletePricingPlanRequest $request, PricingPlan $pricingPlan): array
    {
        return $this->service->deletePricingPlan($pricingPlan);
    }

    /**
     * Pay pricing plan.
     *
     * @param PayPricingPlanRequest $request
     * @param PricingPlan $pricingPlan
     * @return TransactionResource|array
     */
    public function payPricingPlan(PayPricingPlanRequest $request, PricingPlan $pricingPlan): TransactionResource|array
    {
        return $this->service->payPricingPlan($pricingPlan, $request->validated());
    }

    /**
     * Verify pricing plan payment.
     *
     * @param VerifyPricingPlanPaymentRequest $request
     * @param PricingPlan $pricingPlan
     * @param Transaction $transaction
     * @return TransactionResource
     */
    public function verifyPricingPlanPayment(VerifyPricingPlanPaymentRequest $request, PricingPlan $pricingPlan, Transaction $transaction): TransactionResource
    {
        return $this->service->verifyPricingPlanPayment($pricingPlan, $transaction);
    }
}
