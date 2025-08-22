<?php

namespace App\Services;

use App\Models\User;
use App\Enums\CacheName;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;

class UssdService
{
    /**
     * Show profile summary.
     *
     * @return array
     */
    public function showProfileSummary(): array
    {
        /** @var User $user */
        $user = Auth::user();
        return $this->cacheManager($user)->remember(now()->addHour(), function () use ($user) {

            return [
                'id' => $user->id,
                'name' => $user->name,
                'last_name' => $user->last_name,
                'first_name' => $user->first_name,
                'mobile_number' => $user->mobile_number ? PhoneNumberService::formatPhoneNumber($user->mobile_number) : null,
                'stats' => [
                    'stores_joined' => $user->stores()->count(),
                    'stores_following' => $user->followedStores()->count(),
                    'stores_recently_visited' => $user->visitedStores()->count(),
                    'active_billing_schedules' => 0,
                ]
            ];

        });
    }

    /**
     * Get cache manager.
     *
     * @param User $user
     * @return CacheService
     */
    public function cacheManager(User $user): CacheService
    {
        return (new CacheService(CacheName::USSD_HOME))->append($user->id);
    }

    /**
     * Check if valid USSD request.
     *
     * @param User $user
     * @return bool
     */
    public function isValidUssdRequest(): bool
    {
        $ussdToken = request()->header('USSD-Token');
        return filled($ussdToken) && $ussdToken === config('app.ussd_token');
    }

    /**
     *  Get main shortcode e.g *250#
     *
     *  @param string $countryCode
     *  @return string|null
     */
    public static function getMainShortcode(string $countryCode): string|null
    {
        $codes = [
            'bw' => '250'
        ];

        return isset($codes[$countryCode]) ? '*'.$codes[$countryCode].'#' : null;
    }

    /**
     *  Append to main shortcode e.g *123*250#
     *
     *  @param string $countryCode
     *  @return string|null
     */
    public static function appendToMainShortcode(string $number, string $countryCode): string|null
    {
        $number = Str::replace(' ', '', $number);
        return ($mainShortcode = self::getMainShortcode($countryCode)) ? Str::replaceFirst('#', '*'.$number.'#', $mainShortcode) : null;
    }
}
