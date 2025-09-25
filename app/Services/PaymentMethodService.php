<?php

namespace App\Services;

use Exception;
use App\Models\Store;
use App\Enums\Association;
use App\Models\PaymentMethod;
use App\Enums\PaymentMethodType;
use App\Http\Resources\PaymentMethodResource;
use App\Http\Resources\PaymentMethodResources;

class PaymentMethodService extends BaseService
{
    /**
     * Show payment methods.
     *
     * @param array $data
     * @return PaymentMethodResources|array
     */
    public function showPaymentMethods(array $data): PaymentMethodResources|array
    {
        $storeId = $data['store_id'] ?? null;
        $automatedVerification = $data['automated_verification'] ?? null;

        if($storeId) {

            $store = Store::find($storeId);
            $association = isset($data['association']) ? Association::tryFrom($data['association']) : null;

            if($association == Association::UNASSOCIATED) {

                $existingPaymentMethodTypes = $store->paymentMethods()->pluck('type');

                $excludeTypes = [
                    PaymentMethodType::DPO->value,
                    ...$existingPaymentMethodTypes,
                    PaymentMethodType::ORANGE_AIRTIME->value
                ];

                $query = PaymentMethod::whereNotIn('type', $excludeTypes);

            }else{

                $query = $store->paymentMethods();

            }

            if (!is_null($automatedVerification)) {
                $query = $query->where('automated_verification', $automatedVerification);
            }

            return $this->setQuery($query->when(!request()->has('_sort'), fn($query) => $query->orderBy('position')))->getOutput();

        }else{

            return $this->setQuery(PaymentMethod::latest())->getOutput();

        }
    }

    /**
     * Create payment method.
     *
     * @param array $data
     * @return array
     */
    public function createPaymentMethod(array $data): array
    {
        $paymentMethod = PaymentMethod::create($data);
        return $this->showCreatedResource($paymentMethod);
    }

    /**
     * Delete payment methods.
     *
     * @param array $paymentMethodIds
     * @return array
     * @throws Exception
     */
    public function deletePaymentMethods(array $paymentMethodIds): array
    {
        $paymentMethods = PaymentMethod::whereIn('id', $paymentMethodIds)->get();

        if ($totalPaymentMethods = $paymentMethods->count()) {

            foreach ($paymentMethods as $paymentMethod) {

                $this->deletePaymentMethod($paymentMethod);

            }

            return ['message' => $totalPaymentMethods . ($totalPaymentMethods == 1 ? ' Payment Method' : ' Payment Methods') . ' deleted'];
        } else {
            throw new Exception('No Payment Methods deleted');
        }
    }

    /**
     * Show payment method.
     *
     * @param PaymentMethod $paymentMethod
     * @return PaymentMethodResource
     */
    public function showPaymentMethod(PaymentMethod $paymentMethod): PaymentMethodResource
    {
        return $this->showResource($paymentMethod);
    }

    /**
     * Update payment method.
     *
     * @param PaymentMethod $paymentMethod
     * @param array $data
     * @return array
     */
    public function updatePaymentMethod(PaymentMethod $paymentMethod, array $data): array
    {
        $paymentMethod->update($data);
        return $this->showUpdatedResource($paymentMethod);
    }

    /**
     * Delete payment method.
     *
     * @param PaymentMethod $paymentMethod
     * @return array
     * @throws Exception
     */
    public function deletePaymentMethod(PaymentMethod $paymentMethod): array
    {
        $deleted = $paymentMethod->delete();

        if ($deleted) {
            return ['message' => 'Payment Method deleted'];
        } else {
            throw new Exception('Payment Method delete unsuccessful');
        }
    }
}
