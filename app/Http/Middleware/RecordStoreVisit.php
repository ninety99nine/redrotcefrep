<?php

namespace App\Http\Middleware;

use Closure;
use App\Models\User;
use App\Models\Store;
use App\Enums\CacheName;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Services\CacheService;
use App\Services\UssdService;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Response;

class RecordStoreVisit
{
    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param Closure $next
     * @return Response
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Proceed with the request first to allow model route binding to resolve the store
        $response = $next($request);

        /** @var User $user */
        $user = Auth::user();

        // Get the resolved store from the route parameter
        $store = $request->route('store');

        // Record the visit if a valid Store instance is found
        if ($store instanceof Store) {

            //  Handle authenticated user
            if($user) {

                $cacheManager = (new CacheService(CacheName::STORE_VISIT))->append($user->id)->append($store->id);

                if(!$cacheManager->has()) {

                    $user->visitedStores()->syncWithoutDetaching([$store->id => [
                        'id' => Str::uuid(),
                        'last_visited_at' => now(),
                    ]]);

                    $cacheManager->put(true, now()->addHour());

                    //  Forget cache
                    (new UssdService)->cacheManager($user)->forget();

                }

            //  Handle unauthenticated user
            }else{

                // Skip tracking if DoNotTrack header is set
                if ($request->header('DNT') !== '1') {

                    // Get guest_id from cookie or X-Guest-ID header, or generate new UUID
                    $guestId = $request->cookie('guest_id') ?? $request->header('X-Guest-ID', Str::uuid()->toString());

                    // Set the guest_id cookie in the response (30-day expiration)
                    $response->withCookie(new Cookie(
                        name: 'guest_id',
                        value: $guestId,
                        expire: now()->addDays(30)->getTimestamp(),
                        path: '/',
                        domain: null,
                        secure: config('session.secure', false),
                        httpOnly: true,
                        sameSite: 'Lax'
                    ));

                    $cacheManager = (new CacheService(CacheName::STORE_VISIT))->append('guest')->append($guestId)->append($store->id);

                    if(!$cacheManager->has()) {

                        $store->visitors()->syncWithoutDetaching([null => [
                            'id' => Str::uuid(),
                            'guest_id' => $guestId,
                            'last_visited_at' => now(),
                        ]]);

                        $cacheManager->put(true, now()->addHour());

                    }

                }
            }

        }

        return $response;
    }
}
