<?php

namespace App\Services;

use Exception;
use App\Models\Store;
use App\Enums\Association;
use App\Models\DesignCard;
use Illuminate\Support\Facades\DB;
use App\Http\Resources\DesignCardResource;
use App\Http\Resources\DesignCardResources;

class DesignCardService extends BaseService
{
    /**
     * Show design cards.
     *
     * @param array $data
     * @return DesignCardResources|array
     */
    public function showDesignCards(array $data): DesignCardResources|array
    {
        $storeId = $data['store_id'] ?? null;

        if ($storeId) {

            $store = Store::find($storeId);
            $association = isset($data['association']) ? Association::tryFrom($data['association']) : null;

            if ($association == Association::SHOPPER) {
                $query = $store->designCards()->active();
            } else {
                $query = $store->designCards();
            }

        }else{

            $query = DesignCard::query();

        }

        return $this->setQuery($query->when(!request()->has('_sort'), fn($query) => $query->orderBy('position')))->getOutput();
    }

    /**
     * Create design card.
     *
     * @param array $data
     * @return array
     */
    public function createDesignCard(array $data): array
    {
        $storeId = $data['store_id'];
        $store = Store::find($storeId);

        $data = array_merge($data, [
            'currency' => $store->currency
        ]);

        $designCard = DesignCard::create($data);

        $this->updateDesignCardArrangement([
            'store_id' => $storeId,
            'design_card_ids' => [$designCard->id]
        ]);

        return $this->showCreatedResource($designCard);
    }

    /**
     * Delete design cards.
     *
     * @param array $designCardIds
     * @return array
     * @throws Exception
     */
    public function deleteDesignCards(array $designCardIds): array
    {
        $designCards = DesignCard::whereIn('id', $designCardIds)->get();

        if ($totalDesignCards = $designCards->count()) {

            foreach ($designCards as $designCard) {

                $this->deleteDesignCard($designCard);

            }

            return ['message' => $totalDesignCards . ($totalDesignCards == 1 ? ' Design Card' : ' Design Cards') . ' deleted'];
        } else {
            throw new Exception('No Design Cards deleted');
        }
    }

    /**
     * Update design card arrangement.
     *
     * @param array $data
     * @return array
     */
    public function updateDesignCardArrangement(array $data): array
    {
        $storeId = $data['store_id'];
        $store = Store::find($storeId);

        $designCards = $store->designCards()->orderBy('position', 'asc')->get();
        $designCardIds = $data['design_card_ids'];

        $originalDesignCardPositions = $designCards->pluck('position', 'id');

        $arrangement = collect($designCardIds)->filter(function ($designCardId) use ($originalDesignCardPositions) {
            return collect($originalDesignCardPositions)->keys()->contains($designCardId);
        })->toArray();

        $movedDesignCardPositions = collect($arrangement)->mapWithKeys(function ($designCardId, $newPosition) {
            return [$designCardId => ($newPosition + 1)];
        })->toArray();

        $adjustedOriginalDesignCardPositions = $originalDesignCardPositions->except(collect($movedDesignCardPositions)->keys())->keys()->mapWithKeys(function ($id, $index) use ($movedDesignCardPositions) {
            return [$id => count($movedDesignCardPositions) + $index + 1];
        })->toArray();

        $designCardPositions = array_merge($movedDesignCardPositions, $adjustedOriginalDesignCardPositions);

        if (count($designCardPositions)) {
            DB::table('design_cards')
                ->where('store_id', $store->id)
                ->whereIn('id', array_keys($designCardPositions))
                ->update(['position' => DB::raw('CASE id ' . implode(' ', array_map(function ($id, $position) {
                    return 'WHEN "' . $id . '" THEN ' . $position . ' ';
                }, array_keys($designCardPositions), $designCardPositions)) . 'END')]);

            return ['message' => 'Design card arrangement has been updated'];
        }

        return ['message' => 'No matching design cards to update'];
    }

    /**
     * Show design card.
     *
     * @param DesignCard $designCard
     * @return DesignCardResource
     */
    public function showDesignCard(DesignCard $designCard): DesignCardResource
    {
        return $this->showResource($designCard);
    }

    /**
     * Update design card.
     *
     * @param DesignCard $designCard
     * @param array $data
     * @return array
     */
    public function updateDesignCard(DesignCard $designCard, array $data): array
    {
        $designCard->update($data);
        return $this->showUpdatedResource($designCard);
    }

    /**
     * Delete design card.
     *
     * @param DesignCard $designCard
     * @return array
     * @throws Exception
     */
    public function deleteDesignCard(DesignCard $designCard): array
    {
        $deleted = $designCard->delete();

        if ($deleted) {
            return ['message' => 'Design Card deleted'];
        } else {
            throw new Exception('Design Card delete unsuccessful');
        }
    }
}
