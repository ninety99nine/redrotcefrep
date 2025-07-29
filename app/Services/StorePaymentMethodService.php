<?php

namespace App\Services;

use Exception;
use App\Models\Store;
use App\Models\PaymentMethod;
use App\Enums\UploadFolderName;
use App\Enums\PaymentMethodType;
use App\Models\StorePaymentMethod;
use Illuminate\Support\Facades\DB;
use App\Http\Resources\StorePaymentMethodResource;
use App\Http\Resources\StorePaymentMethodResources;

class StorePaymentMethodService extends BaseService
{
    /**
     * Show store payment methods.
     *
     * @param array $data
     * @return StorePaymentMethodResources|array
     */
    public function showStorePaymentMethods(array $data): StorePaymentMethodResources|array
    {
        $storeId = $data['store_id'] ?? null;

        if($storeId) {
            $query = StorePaymentMethod::query()->where('store_id', $storeId)->when(!request()->has('_sort'), fn($query) => $query->orderBy('position'));
        }else{
            $query = StorePaymentMethod::query()->when(!request()->has('_sort'), fn($query) => $query->orderBy('position'));
        }

        return $this->setQuery($query)->getOutput();
    }

    /**
     * Create store payment method.
     *
     * @param array $data
     * @return array
     */
    public function createStorePaymentMethod(array $data): array
    {
        $paymentMethod = PaymentMethod::find($data['payment_method_id']);

        $data['configs'] = $data['configs'] ? collect($data['configs'])->reject(function ($value, $key) {
            return in_array($key, ['logo', 'photo']);
        })->toArray() : null;

        if ($paymentMethod->type === PaymentMethodType::OTHER->value) {

            $storePaymentMethod = StorePaymentMethod::create($data);

        } else {

            $existingStorePaymentMethod = StorePaymentMethod::where('store_id', $data['store_id'])->where('payment_method_id', $data['payment_method_id'])->first();

            if ($existingStorePaymentMethod) {
                $existingStorePaymentMethod->update($data);
            }else{
                $storePaymentMethod = StorePaymentMethod::create($data);
            }

        }

        // Create logo if provided
        if (isset($data['logo']) && !empty($data['logo'])) {
            (new MediaFileService)->createMediaFile([
                'file' => $data['logo'],
                'mediable_id' => $storePaymentMethod->id,
                'mediable_type' => 'store_payment_method',
                'upload_folder_name' => UploadFolderName::STORE_PAYMENT_METHOD_LOGO->value
            ]);
        }

        // Create photo if provided
        if (isset($data['photo']) && !empty($data['photo'])) {
            (new MediaFileService)->createMediaFile([
                'file' => $data['photo'],
                'mediable_id' => $storePaymentMethod->id,
                'mediable_type' => 'store_payment_method',
                'upload_folder_name' => UploadFolderName::STORE_PAYMENT_METHOD_PHOTO->value
            ]);
        }

        return $this->showCreatedResource($storePaymentMethod);
    }

    /**
     * Delete store payment methods.
     *
     * @param string $storeId
     * @param array $storePaymentMethodIds
     * @return array
     * @throws Exception
     */
    public function deleteStorePaymentMethods(string $storeId, array $storePaymentMethodIds): array
    {
        $storePaymentMethods = StorePaymentMethod::where('store_id', $storeId)
            ->whereIn('id', $storePaymentMethodIds)
            ->get();

        if ($totalStorePaymentMethods = $storePaymentMethods->count()) {

            $mediaFileService = new MediaFileService;

            foreach ($storePaymentMethods as $storePaymentMethod) {

                foreach ($storePaymentMethod->mediaFiles as $mediaFile) {
                    $mediaFileService->deleteMediaFile($mediaFile);
                }

                $storePaymentMethod->delete();

            }

            return ['message' => $totalStorePaymentMethods . ($totalStorePaymentMethods == 1 ? ' Store Payment Method' : ' Store Payment Methods') . ' deleted'];

        } else {
            throw new Exception('No Store Payment Methods deleted');
        }
    }

    /**
     * Update store payment method arrangement
     *
     * @param array $data
     * @return array
     */
    public function updateStorePaymentMethodArrangement(array $data): array
    {
        $storeId = $data['store_id'];
        $store = Store::find($storeId);
        $storePaymentMethodIds = $data['store_payment_method_ids'];

        $storePaymentMethods = $store->storePaymentMethods->get();
        $originalStorePaymentMethodPositions = $storePaymentMethods->pluck('position', 'id');

        $arrangement = collect($storePaymentMethodIds)->filter(function ($StorePaymentMethodId) use ($originalStorePaymentMethodPositions) {
            return collect($originalStorePaymentMethodPositions)->keys()->contains($StorePaymentMethodId);
        })->toArray();

        $movedStorePaymentMethodPositions = collect($arrangement)->mapWithKeys(function ($StorePaymentMethodId, $newPosition) use ($originalStorePaymentMethodPositions) {
            return [$StorePaymentMethodId => ($newPosition + 1)];
        })->toArray();

        $adjustedOriginalStorePaymentMethodPositions = $originalStorePaymentMethodPositions->except(collect($movedStorePaymentMethodPositions)->keys())->keys()->mapWithKeys(function ($id, $index) use ($movedStorePaymentMethodPositions) {
            return [$id => count($movedStorePaymentMethodPositions) + $index + 1];
        })->toArray();

        $storePaymentMethodPositions = $movedStorePaymentMethodPositions + $adjustedOriginalStorePaymentMethodPositions;

        if(count($storePaymentMethodPositions)) {

            DB::table('store_payment_method')
                ->whereIn('id', array_keys($storePaymentMethodPositions))
                ->update(['position' => DB::raw('CASE id ' . implode(' ', array_map(function ($id, $position) {
                    return 'WHEN "' . $id . '" THEN ' . $position . ' ';
                }, array_keys($storePaymentMethodPositions), $storePaymentMethodPositions)) . 'END')]);

            return ['message' => 'Store payment method arrangement has been updated'];

        }

        return ['message' => 'No matching store payment methods to update'];
    }

    /**
     * Show store payment method.
     *
     * @param StorePaymentMethod $storePaymentMethod
     * @return StorePaymentMethodResource
     */
    public function showStorePaymentMethod(StorePaymentMethod $storePaymentMethod): StorePaymentMethodResource
    {
        return $this->showResource($storePaymentMethod);
    }

    /**
     * Update store payment method.
     *
     * @param StorePaymentMethod $storePaymentMethod
     * @param array $data
     * @return array
     */
    public function updateStorePaymentMethod(StorePaymentMethod $storePaymentMethod, array $data): array
    {
        $data['configs'] = $data['configs'] ? collect($data['configs'])->reject(function ($value, $key) {
            return in_array($key, ['logo', 'photo']);
        })->toArray() : null;

        $storePaymentMethod->update($data);

        // Update or create logo if provided
        if (isset($data['logo']) && !empty($data['logo'])) {
            (new MediaFileService)->createMediaFile([
                'file' => $data['logo'],
                'mediable_id' => $storePaymentMethod->id,
                'mediable_type' => 'store_payment_method',
                'upload_folder_name' => UploadFolderName::STORE_PAYMENT_METHOD_LOGO->value
            ]);
        }

        // Update or create photo if provided
        if (isset($data['photo']) && !empty($data['photo'])) {
            (new MediaFileService)->createMediaFile([
                'file' => $data['photo'],
                'mediable_id' => $storePaymentMethod->id,
                'mediable_type' => 'store_payment_method',
                'upload_folder_name' => UploadFolderName::STORE_PAYMENT_METHOD_PHOTO->value
            ]);
        }

        return $this->showUpdatedResource($storePaymentMethod->load(['paymentMethod', 'logo', 'photo']));
    }

    /**
     * Delete store payment method.
     *
     * @param StorePaymentMethod $storePaymentMethod
     * @return array
     * @throws Exception
     */
    public function deleteStorePaymentMethod(StorePaymentMethod $storePaymentMethod): array
    {
        $mediaFileService = new MediaFileService;

        foreach ($storePaymentMethod->mediaFiles as $mediaFile) {
            $mediaFileService->deleteMediaFile($mediaFile);
        }

        $deleted = $storePaymentMethod->delete();

        if ($deleted) {
            return ['message' => 'Store Payment Method deleted'];
        } else {
            throw new Exception('Store Payment Method delete unsuccessful');
        }
    }
}
