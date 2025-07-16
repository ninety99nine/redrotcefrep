<?php

namespace App\Observers;

use App\Models\Store;
use App\Models\StoreQuota;
use Illuminate\Support\Str;
use App\Services\QrCodeService;

class StoreObserver
{
    public function saving(Store $store): void
    {
        $this->setAlias($store);
    }

    public function creating(Store $store): void
    {
        $idBasedWebLink = config('app.url').'/'.$store->id;
        $store->qr_code_file_path = QrCodeService::generate($idBasedWebLink);
    }

    public function created(Store $store): void
    {
        StoreQuota::create(['store_id' => $store->id]);
        /*

        if(!is_null($store->whatsapp_mobile_number)) {
            $store->storeRollingNumbers()->create([
                'mobile_number' => $store->whatsapp_mobile_number
            ]);
        }
        */
    }

    public function updated(Store $store): void
    {
        //
    }

    public function deleting(Store $store): void
    {
        //
    }

    public function deleted(Store $store): void
    {
        //
    }

    public function restored(Store $store): void
    {
        //
    }

    public function forceDeleted(Store $store): void
    {
        //
    }

    /**
     * Set store alias.
     *
     * @param Store $store
     * @return Store
     */
    private function setAlias(Store $store): void
    {
        if (empty($store->alias)) {
            $baseAlias = Str::slug($store->name);
            $similarAliases = Store::where('alias', 'like', "{$baseAlias}%")->pluck('alias')->toArray();

            if (!in_array($baseAlias, $similarAliases)) {
                $store->alias = $baseAlias;
            } else {
                $maxSuffix = $this->getMaxSuffix($baseAlias, $similarAliases);
                $store->alias = "{$baseAlias}-" . ($maxSuffix + 1);
            }
        } else {
            $store->alias = Str::slug($store->alias);
        }
    }

    /**
     * Get the highest numeric suffix for the base alias.
     *
     * @param string $baseAlias
     * @param array $similarAliases
     * @return int
     */
    private function getMaxSuffix(string $baseAlias, array $similarAliases): int
    {
        $maxSuffix = 0;

        foreach ($similarAliases as $alias) {
            if (preg_match('/^' . preg_quote($baseAlias, '/') . '-(\d+)$/', $alias, $matches)) {
                $maxSuffix = max($maxSuffix, (int) $matches[1]);
            }
        }

        return $maxSuffix;
    }
}
