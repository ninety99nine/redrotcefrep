<?php

namespace App\Services;

use Exception;
use Carbon\Carbon;
use App\Models\Store;
use App\Enums\Association;
use App\Models\DeliveryMethod;
use Illuminate\Support\Facades\DB;
use App\Enums\DeliveryMethodScheduleType;
use App\Http\Resources\DeliveryMethodResource;
use App\Http\Resources\DeliveryMethodResources;

class DeliveryMethodService extends BaseService
{
    /**
     * Show delivery methods.
     *
     * @param array $data
     * @return DeliveryMethodResources|array
     */
    public function showDeliveryMethods(array $data): DeliveryMethodResources|array
    {
        $storeId = $data['store_id'] ?? null;
        $association = isset($data['association']) ? Association::tryFrom($data['association']) : null;

        if ($association == Association::SUPER_ADMIN || $association == Association::TEAM_MEMBER) {
            $query = DeliveryMethod::query();
        }else {
            $query = DeliveryMethod::query()->where('active', '1');
        }

        if ($storeId) {
            $query = $query->where('store_id', $storeId);
        }

        $query = $query->when(!request()->has('_sort'), fn($query) => $query->orderBy('position'));
        return $this->setQuery($query)->getOutput();
    }

    /**
     * Create delivery method.
     *
     * @param array $data
     * @return array
     */
    public function createDeliveryMethod(array $data): array
    {
        $storeId = $data['store_id'];
        $store = Store::find($storeId);

        $data = array_merge($data, [
            'currency' => $store->currency
        ]);

        $deliveryMethod = DeliveryMethod::create($data);

        $this->updateDeliveryMethodArrangement([
            'store_id' => $storeId,
            'delivery_method_ids' => [$deliveryMethod->id]
        ]);

        return $this->showCreatedResource($deliveryMethod);
    }

    /**
     * Delete delivery methods.
     *
     * @param array $deliveryMethodIds
     * @return array
     * @throws Exception
     */
    public function deleteDeliveryMethods(array $deliveryMethodIds): array
    {
        $deliveryMethods = DeliveryMethod::whereIn('id', $deliveryMethodIds)->get();

        if ($totalDeliveryMethods = $deliveryMethods->count()) {

            foreach ($deliveryMethods as $deliveryMethod) {

                $this->deleteDeliveryMethod($deliveryMethod);

            }

            return ['message' => $totalDeliveryMethods . ($totalDeliveryMethods == 1 ? ' Delivery Method' : ' Delivery Methods') . ' deleted'];
        } else {
            throw new Exception('No Delivery Methods deleted');
        }
    }

    /**
     * Update delivery method arrangement.
     *
     * @param array $data
     * @return array
     */
    public function updateDeliveryMethodArrangement(array $data): array
    {
        $storeId = $data['store_id'];
        $store = Store::find($storeId);

        $deliveryMethods = $store->deliveryMethods()->orderBy('position', 'asc')->get();
        $deliveryMethodIds = $data['delivery_method_ids'];

        $originalDeliveryMethodPositions = $deliveryMethods->pluck('position', 'id');

        $arrangement = collect($deliveryMethodIds)->filter(function ($deliveryMethodId) use ($originalDeliveryMethodPositions) {
            return collect($originalDeliveryMethodPositions)->keys()->contains($deliveryMethodId);
        })->toArray();

        $movedDeliveryMethodPositions = collect($arrangement)->mapWithKeys(function ($deliveryMethodId, $newPosition) {
            return [$deliveryMethodId => ($newPosition + 1)];
        })->toArray();

        $adjustedOriginalDeliveryMethodPositions = $originalDeliveryMethodPositions->except(collect($movedDeliveryMethodPositions)->keys())->keys()->mapWithKeys(function ($id, $index) use ($movedDeliveryMethodPositions) {
            return [$id => count($movedDeliveryMethodPositions) + $index + 1];
        })->toArray();

        $deliveryMethodPositions = array_merge($movedDeliveryMethodPositions, $adjustedOriginalDeliveryMethodPositions);

        if (count($deliveryMethodPositions)) {
            DB::table('delivery_methods')
                ->where('store_id', $store->id)
                ->whereIn('id', array_keys($deliveryMethodPositions))
                ->update(['position' => DB::raw('CASE id ' . implode(' ', array_map(function ($id, $position) {
                    return 'WHEN "' . $id . '" THEN ' . $position . ' ';
                }, array_keys($deliveryMethodPositions), $deliveryMethodPositions)) . 'END')]);

            return ['message' => 'Delivery method arrangement has been updated'];
        }

        return ['message' => 'No matching delivery methods to update'];
    }

    /**
     * Show delivery method schedule options.
     *
     * @param array $data
     * @return array
     */
    public function showDeliveryMethodScheduleOptions(array $data): array
    {
        $data['set_schedule'] = true;
        $deliveryDate = $data['delivery_date'] ?? null;
        $showAllDates = $data['show_all_dates'] ?? null;
        $showAllTimeslots = $data['show_all_timeslots'] ?? null;

        $deliveryMethod = new DeliveryMethod();
        $deliveryMethod->fill($data);

        $scheduleOptions = [
            'delivery_message' => null,
            'schedule_key_points' => [],
            'available_time_slots' => [],
            'min_date' => $showAllDates ? null : $deliveryMethod->minDate(),
            'max_date' => $showAllDates ? null : $deliveryMethod->maxDate(),
            'dates_disabled' => $showAllDates ? [] : $deliveryMethod->datesDisabled(),
            'days_of_week_disabled' => $showAllDates ? [] : $deliveryMethod->daysOfWeekDisabled(),
        ];

        if ($deliveryMethod->same_day_delivery) {
            $scheduleOptions['schedule_key_points'][] = 'Deliveries must be the same day';
        }

        $availableDays = collect($deliveryMethod->operational_hours)
            ->filter(fn($day) => $showAllDates ?? $day['available'])
            ->keys()
            ->map(fn($dayIndex) => Carbon::create()->startOfWeek()->addDays($dayIndex)->format('l'))
            ->toArray();

        if (empty($availableDays)) {
            $scheduleOptions['schedule_key_points'][] = 'Deliveries are not allowed on any day of the week';
        } elseif (count($availableDays) == 7) {
            $scheduleOptions['schedule_key_points'][] = 'Deliveries are allowed on all days of the week';
        } else {
            $formattedDays = count($availableDays) > 1
                ? implode(', ', array_slice($availableDays, 0, -1)) . ' and ' . end($availableDays)
                : $availableDays[0];

            $scheduleOptions['schedule_key_points'][] = sprintf(
                'Deliveries are allowed on %d %s of the week: %s',
                count($availableDays), count($availableDays) == 1 ? 'day' : 'days', $formattedDays
            );
        }

        if ($deliveryMethod->schedule_type == DeliveryMethodScheduleType::DATE->value) {
            $scheduleOptions['schedule_key_points'][] = 'Customers must specify only date without the time for delivery';
        } else {
            $scheduleOptions['schedule_key_points'][] = 'Customers must specify both date and time for delivery';
        }

        if ($deliveryMethod->schedule_type == DeliveryMethodScheduleType::DATE_AND_TIME->value && $deliveryMethod->auto_generate_time_slots) {
            $scheduleOptions['schedule_key_points'][] = 'Auto generate additional time options between the specified timeslots for each day of the week';
        }

        // Minimum notice for orders
        if (!$showAllDates && $deliveryMethod->require_minimum_notice_for_orders && $deliveryMethod->earliest_delivery_time_value > 0) {
            $unit = $deliveryMethod->earliest_delivery_time_unit;
            $value = $deliveryMethod->earliest_delivery_time_value;
            $unitText = $value == 1 ? $unit : $unit . 's';

            $scheduleOptions['schedule_key_points'][] = sprintf(
                'Orders must be placed at least %d %s before the delivery date (%d %s notice)',
                $value, $unitText, $value, $unitText
            );
        }

        // Maximum notice for orders
        if (!$showAllDates && $deliveryMethod->restrict_maximum_notice_for_orders && $deliveryMethod->latest_delivery_time_value > 0) {
            $value = $deliveryMethod->latest_delivery_time_value;
            $unitText = $value == 1 ? 'day' : 'days';

            $scheduleOptions['schedule_key_points'][] = sprintf(
                'Orders cannot be scheduled for delivery more than %d %s in advance',
                $value, $unitText
            );
        }

        // Delivery message
        if ($deliveryDate && $deliveryMethod->schedule_type == DeliveryMethodScheduleType::DATE_AND_TIME->value) {
            $isValidDate = $showAllDates || $deliveryMethod->isValidDate($deliveryDate);

            if ($isValidDate) {
                $scheduleOptions['available_time_slots'] =
                    $showAllTimeslots
                        ? $deliveryMethod->allTimeSlots($deliveryDate)
                        : $deliveryMethod->availableTimeSlots($deliveryDate);

                $deliveryDate = Carbon::parse($deliveryDate);

                $scheduleOptions['delivery_message'] = sprintf(
                    'Your order will be delivered on %s (%s), just %s from now.',
                    $deliveryDate->format('d M Y'),
                    $deliveryDate->format('D'),
                    $deliveryDate->diffForHumans(null, true)
                );
            }
        }

        return $scheduleOptions;
    }

    /**
     * Show delivery method.
     *
     * @param DeliveryMethod $deliveryMethod
     * @return DeliveryMethodResource
     */
    public function showDeliveryMethod(DeliveryMethod $deliveryMethod): DeliveryMethodResource
    {
        return $this->showResource($deliveryMethod);
    }

    /**
     * Update delivery method.
     *
     * @param DeliveryMethod $deliveryMethod
     * @param array $data
     * @return array
     */
    public function updateDeliveryMethod(DeliveryMethod $deliveryMethod, array $data): array
    {
        $deliveryMethod->update($data);
        return $this->showUpdatedResource($deliveryMethod);
    }

    /**
     * Delete delivery method.
     *
     * @param DeliveryMethod $deliveryMethod
     * @return array
     * @throws Exception
     */
    public function deleteDeliveryMethod(DeliveryMethod $deliveryMethod): array
    {
        $deleted = $deliveryMethod->delete();

        if ($deleted) {
            return ['message' => 'Delivery Method deleted'];
        } else {
            throw new Exception('Delivery Method delete unsuccessful');
        }
    }
}
