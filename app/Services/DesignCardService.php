<?php

namespace App\Services;

use Exception;
use App\Models\Store;
use App\Models\Address;
use App\Models\Category;
use App\Models\DesignCard;
use App\Enums\DesignCardType;
use App\Enums\SocialMediaLink;
use App\Enums\UploadFolderName;
use Illuminate\Support\Facades\DB;
use App\Enums\DesignCardPlacement;
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
        $placement = isset($data['placement']) ? DesignCardPlacement::tryFrom($data['placement']) : null;

        $query = DesignCard::query();

        if ($placement) {
            $query = $query->where('placement', $placement->value);
        }

        if ($storeId) {
            $query = $query->where('store_id', $storeId);
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
        $address = $data['address'] ?? null;
        $arrangement = $data['arrangement'] ?? 'first';

        $data = array_merge($data, [
            'currency' => $store->currency
        ]);

        $designCard = DesignCard::create($data);

        if(!is_null($address)) {

            $address = array_merge($address, [
                'owner_type' => 'design card',
                'owner_id' => $designCard->id
            ]);

            Address::create($address);

        }

        if (isset($data['photo']) && !empty($data['photo'])) {

            (new MediaFileService)->createMediaFile([
                'file' => $data['photo'],
                'mediable_type' => 'design card',
                'mediable_id' => $designCard->id,
                'upload_folder_name' => UploadFolderName::DESIGN_CARD_PHOTO->value
            ]);

        }

        if($arrangement == 'first') {

            $this->updateDesignCardArrangement([
                'store_id' => $storeId,
                'design_card_ids' => [$designCard->id]
            ]);

        }else{

            $otherDesignCardIds = DesignCard::where('id', '!=', $designCard->id)
                                    ->where('placement', $designCard->placement)
                                    ->where('store_id', $storeId)
                                    ->orderBy('position', 'asc')
                                    ->pluck('id');

            $this->updateDesignCardArrangement([
                'store_id' => $storeId,
                'design_card_ids' => [...$otherDesignCardIds, $designCard->id]
            ]);

        }

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
     * Show design card configurations.
     *
     * @param array $data
     * @return array
     */
    public function showDesignCardConfigurations(array $data): array
    {
        $storeId = $data['store_id'];
        $type = isset($data['type']) ? DesignCardType::tryFrom($data['type']) : null;

        $types = $type ? [$type] : DesignCardType::cases();
        $categoryIds = Category::where('store_id', $storeId)->pluck('id');

        $configs = array_map(function ($designCardType) use ($categoryIds) {

            $metadata = match ($designCardType) {
                DesignCardType::LOGO => [
                    'alignment' => 'center',
                    'design' => array_merge($this->getDefaultDesign(), [
                        't_margin' => '0',
                        'l_margin' => '0',
                        'r_margin' => '0',
                        'tl_border_radius' => '0',
                        'br_border_radius' => '0',
                        'tr_border_radius' => '0',
                        'bl_border_radius' => '0',
                        'bg_color' => null,
                    ]),
                ],
                DesignCardType::PRODUCTS => [
                    'category_id' => $categoryIds[0] ?? null,
                    'layout' => 'grid',
                    'feature' => '4',
                    'design' => array_merge($this->getDefaultDesign(), [
                        'tl_border_radius' => '8',
                        'br_border_radius' => '8',
                        'tr_border_radius' => '8',
                        'bl_border_radius' => '8',
                        'description_color' => '#4B5563',
                        'product_name_color' => '#111827',
                        'product_price_color' => '#111827',
                        'product_cancelled_price_color' => '#9CA3AF',
                    ]),
                ],
                DesignCardType::LINK => [
                    'title' => 'Our Blog',
                    'link' => 'https://www.example.com',
                    'design' => array_merge($this->getDefaultDesign(), [
                        't_border' => '1',
                        'b_border' => '1',
                        'l_border' => '1',
                        'r_border' => '1',
                        'icon_color' => '#6B7280',
                    ]),
                ],
                DesignCardType::TEXT => [
                    'body' => "# Heading 1\n## Heading 2\n### Heading 3\n**bold**\n~strike~\n_italic_\n**_Bold italic_**",
                    'design' => array_merge($this->getDefaultDesign(), [
                        'tl_border_radius' => '8',
                        'br_border_radius' => '8',
                        'tr_border_radius' => '8',
                        'bl_border_radius' => '8',
                        'text_color' => '#111827',
                    ]),
                ],
                DesignCardType::IMAGE => [
                    'link' => '',
                    'upper_text' => '',
                    'lower_text' => '',
                    'design' => array_merge($this->getDefaultDesign(), [
                        'text_color' => '#111827',
                    ]),
                ],
                DesignCardType::VIDEO => [
                    'title' => 'Our Video',
                    'link' => 'https://www.youtube.com/watch?v=eHJnEHyyN1Y',
                    'design' => array_merge($this->getDefaultDesign(), [
                        'icon_color' => '#6B7280',
                    ]),
                ],
                DesignCardType::CONTACT => [
                    'title' => 'Contact Us',
                    'mobile_number' => '+26772000001',
                    'design' => array_merge($this->getDefaultDesign(), [
                        't_border' => '1',
                        'b_border' => '1',
                        'l_border' => '1',
                        'r_border' => '1',
                        'icon_color' => '#6B7280',
                    ]),
                ],
                DesignCardType::COUNTDOWN => [
                    'date' => '',
                    'upper_text' => '',
                    'lower_text' => '',
                    'design' => array_merge($this->getDefaultDesign(), [
                        'text_color' => '#111827',
                        'countdown_text_color' => '#111827',
                    ]),
                ],
                DesignCardType::MAP => [
                    'address' => null,
                    'upper_text' => '',
                    'lower_text' => '',
                    'show_address' => true,
                    'design' => array_merge($this->getDefaultDesign(), [
                        'text_color' => '#111827',
                        'address_color' => '#111827',
                    ]),
                ],
                DesignCardType::SOCIALS => [
                    'title' => '',
                    'show_more' => false,
                    'platforms' => collect(SocialMediaLink::values())->map(fn($platform) => ['name' => $platform, 'link' => '']),
                    'design' => array_merge($this->getDefaultDesign(), [
                        'icon_color' => '#6B7280',
                    ]),
                ],
                DesignCardType::DIVIDER => [
                    'divider' => 'solid',
                    'thickness' => '1',
                    'design' => array_merge($this->getDefaultDesign(), [
                        't_margin' => '8',
                        'l_margin' => '0',
                        'r_margin' => '0',
                        't_padding' => '0',
                        'b_padding' => '0',
                        'l_padding' => '0',
                        'r_padding' => '0',
                        'tl_border_radius' => '0',
                        'br_border_radius' => '0',
                        'tr_border_radius' => '0',
                        'bl_border_radius' => '0',
                        'bg_color' => null,
                        'divider_color' => '#6B7280',
                    ]),
                ],
                DesignCardType::BANNER => [
                    'title' => 'Join Our Whatsapp Group',
                    'link' => 'https://wa.me',
                    'design' => array_merge($this->getDefaultDesign(), [
                        't_margin' => '0',
                        'l_margin' => '0',
                        'r_margin' => '0',
                        'tl_border_radius' => '0',
                        'br_border_radius' => '0',
                        'tr_border_radius' => '0',
                        'bl_border_radius' => '0',
                        'bg_color' => '#01a045',
                        'text_color' => '#ffffff',
                    ]),
                ],
                DesignCardType::CATEGORIES => [
                    'title' => 'Categories',
                    'design' => array_merge($this->getDefaultDesign(), [
                        't_margin' => '8',
                        'l_margin' => '0',
                        'r_margin' => '0',
                        't_padding' => '0',
                        'b_padding' => '0',
                        'l_padding' => '0',
                        'r_padding' => '0',
                        'tl_border_radius' => '0',
                        'br_border_radius' => '0',
                        'tr_border_radius' => '0',
                        'bl_border_radius' => '0',
                        'bg_color' => null,
                    ]),
                ],
                DesignCardType::INSTALL_APP => [
                    'button_text' => 'Install Our App',
                    'design' => array_merge($this->getDefaultDesign(), [
                        't_margin' => '8',
                        'l_margin' => '0',
                        'r_margin' => '0',
                        't_padding' => '0',
                        'b_padding' => '0',
                        'l_padding' => '0',
                        'r_padding' => '0',
                        'tl_border_radius' => '0',
                        'br_border_radius' => '0',
                        'tr_border_radius' => '0',
                        'bl_border_radius' => '0',
                        'button_color' => '#1E40AF',
                        'button_text_color' => '#ffffff',
                    ]),
                ],
                DesignCardType::SHORT_ANSWER,
                DesignCardType::LONG_ANSWER,
                DesignCardType::NUMBER,
                DesignCardType::DATE,
                DesignCardType::TIME => [
                    'title' => match ($designCardType) {
                        DesignCardType::SHORT_ANSWER => 'Enter a short answer',
                        DesignCardType::LONG_ANSWER => 'Enter a long answer',
                        DesignCardType::NUMBER => 'Enter a number',
                        DesignCardType::DATE => 'Select a date',
                        DesignCardType::TIME => 'Select a time',
                    },
                    'description' => '',
                    'required' => false,
                    'design' => array_merge($this->getDefaultDesign(), [
                        'description_color' => '#9CA3AF',
                        'optional_text_color' => '#9CA3AF',
                    ]),
                ],
                DesignCardType::CHECKBOX => [
                    'min' => '1',
                    'max' => '2',
                    'title' => 'Select an option',
                    'description' => '',
                    'options' => [],
                    'required' => false,
                    'validation' => 'not applicable',
                    'design' => array_merge($this->getDefaultDesign(), [
                        'checkbox_color' => '#111827',
                        'description_color' => '#9CA3AF',
                        'optional_text_color' => '#9CA3AF',
                    ]),
                ],
                DesignCardType::SELECTION => [
                    'title' => 'Select an option',
                    'description' => '',
                    'options' => [],
                    'required' => false,
                    'design' => array_merge($this->getDefaultDesign(), [
                        'radio_color' => '#111827',
                        'description_color' => '#9CA3AF',
                        'optional_text_color' => '#9CA3AF',
                    ]),
                ],
                DesignCardType::MEDIA => [
                    'title' => 'Attach an image',
                    'description' => '',
                    'required' => false,
                    'design' => array_merge($this->getDefaultDesign(), [
                        'media_bg_color' => null,
                        'media_text_color' => '#111827',
                        'description_color' => '#9CA3AF',
                        'optional_text_color' => '#9CA3AF',
                    ]),
                ],
                DesignCardType::LOCATION => [
                    'title' => 'Select location',
                    'description' => '',
                    'trigger_text' => 'Add Address',
                    'required' => false,
                    'design' => array_merge($this->getDefaultDesign(), [
                        'description_color' => '#9CA3AF',
                        'optional_text_color' => '#9CA3AF',
                    ]),
                ],
                DesignCardType::CUSTOMER => [
                    'title' => 'Customer',
                    'description' => '',
                    'show_first_name' => true,
                    'first_name_required' => true,
                    'show_last_name' => false,
                    'last_name_required' => false,
                    'show_email' => false,
                    'email_required' => false,
                    'show_mobile_number' => false,
                    'mobile_number_required' => false,
                    'design' => array_merge($this->getDefaultDesign(), [
                        'description_color' => '#111827',
                        'optional_text_color' => '#9CA3AF',
                    ]),
                ],
                DesignCardType::ITEMS => [
                    'title' => 'Items',
                    'description' => '',
                    'show_items' => true,
                    'design' => array_merge($this->getDefaultDesign(), [
                        'description_color' => '#111827',
                    ]),
                ],
                DesignCardType::DELIVERY => [
                    'title' => 'Delivery Methods',
                    'description' => '',
                    'show_delivery_methods' => true,
                    'schedule_title' => 'Schedule',
                    'address_title' => 'Address',
                    'design' => array_merge($this->getDefaultDesign(), [
                        'description_color' => '#111827',
                    ]),
                ],
                DesignCardType::PROMO_CODE => [
                    'title' => 'Promo code',
                    'description' => '',
                    'show_promo_code' => true,
                    'design' => array_merge($this->getDefaultDesign(), [
                        'description_color' => '#111827',
                    ]),
                ],
                DesignCardType::TIPS => [
                    'title' => 'Tip',
                    'description' => '',
                    'tips' => ['5', '10'],
                    'show_tips' => true,
                    'show_specify_tip' => true,
                    'design' => array_merge($this->getDefaultDesign(), [
                        'pill_bg_color' => '#DBEAFE',
                        'pill_text_color' => '#1E40AF',
                        'description_color' => '#111827',
                    ]),
                ],
                DesignCardType::ORDER_SUMMARY => [
                    'title' => 'Order Summary',
                    'description' => '',
                    'checkout_fees' => [],
                    'combine_fees' => false,
                    'combine_discounts' => false,
                    'design' => array_merge($this->getDefaultDesign(), [
                        'description_color' => '#111827',
                    ]),
                ],
                DesignCardType::PAYMENT_METHODS => [
                    'title' => 'Complete Your Payment',
                    'subtitle' => 'Amount to pay',
                    'design' => array_merge($this->getDefaultDesign(), [
                        'bg_color' => null,
                        'subtitle_color' => '#111827',
                        'amount_color' => '#111827',
                    ]),
                ],
            };

            $config = [
                'metadata' => $metadata,
                'type' => $designCardType->value,
            ];

            if (in_array($designCardType, [DesignCardType::MAP])) {
                $config['address'] = null;
            }

            if (in_array($designCardType, [DesignCardType::IMAGE, DesignCardType::COUNTDOWN])) {
                $config['photos'] = [];
            }

            return $config;

        }, $types);

        return $configs;
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
        $mediaFileService = new MediaFileService;

        foreach ($designCard->mediaFiles as $mediaFile) {
            $mediaFileService->deleteMediaFile($mediaFile);
        }

        $deleted = $designCard->delete();

        if ($deleted) {
            return ['message' => 'Design Card deleted'];
        } else {
            throw new Exception('Design Card delete unsuccessful');
        }
    }

    /**
     * Default design properties used across most design card types.
     *
     * @return array
     */
    protected function getDefaultDesign(): array
    {
        return [
            't_margin' => '0',
            'b_margin' => '8',
            'l_margin' => '4',
            'r_margin' => '4',
            't_padding' => '16',
            'b_padding' => '16',
            'l_padding' => '16',
            'r_padding' => '16',
            'tl_border_radius' => '16',
            'br_border_radius' => '16',
            'tr_border_radius' => '16',
            'bl_border_radius' => '16',
            't_border' => '0',
            'b_border' => '0',
            'l_border' => '0',
            'r_border' => '0',
            'border_color' => '#E5E7EB',
            'bg_color' => '#ffffff',
            'title_color' => '#111827',
        ];
    }
}
