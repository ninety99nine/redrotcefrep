<?php

namespace App\Http\Middleware;

use Closure;
use App\Models\User;
use App\Models\Store;
use App\Models\PageView;
use App\Enums\CacheName;
use App\Models\StoreVisitor;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Services\CacheService;
use App\Services\UssdService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
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
        $store = $request->route('store');

        if ($store instanceof Store /* && $request->header('DNT') !== '1' */) {

            // Get or generate session ID
            $sessionId = $user ? $user->id : ($request->cookie('guest_id') ?? $request->header('X-Guest-ID', Str::uuid()->toString()));

            // Optimize cookie setting
            if (!$user && !$request->cookie('guest_id')) {

                $response->withCookie(new Cookie(
                    name: 'guest_id',
                    value: $sessionId,
                    expire: now()->addDays(30)->getTimestamp(),
                    path: '/',
                    domain: null,
                    secure: config('session.secure', false),
                    httpOnly: true,
                    sameSite: 'Lax'
                ));

            }

            // Validate frontend headers
            $pageName = $request->headers->get('frontend-page-name');
            $pageUrl = $request->headers->get('frontend-page-url') ?? null;

            if(!empty($pageName)) {

                // Record page view
                $cacheManager = (new CacheService(CacheName::PAGE_VIEW))->append($sessionId)->append($store->id)->append($pageName);

                if (!$cacheManager->has()) {

                    try {

                        PageView::create([
                            'id' => Str::uuid(),
                            'url' => $pageUrl,
                            'name' => $pageName,
                            'store_id' => $store->id,
                            'session_id' => $sessionId,
                            'referrer' => $request->header('referer')
                        ]);

                        $cacheManager->put(true, now()->addHour());

                    } catch (\Exception $e) {

                        Log::error('Failed to record page view: ' . $e->getMessage());

                    }
                }

                // Record store visit
                if ($user) {

                    $cacheManager = (new CacheService(CacheName::STORE_VISIT))->append($user->id)->append($store->id);

                    if (!$cacheManager->has()) {

                        try {

                            StoreVisitor::updateOrCreate(
                                [
                                    'store_id' => $store->id,
                                    'user_id' => $user->id,
                                    'guest_id' => null
                                ],
                                [
                                    'id' => Str::uuid(),
                                    'last_visited_at' => now()
                                ]
                            );

                            $cacheManager->put(true, now()->addHour());

                            (new UssdService)->cacheManager($user)->forget();

                        } catch (\Exception $e) {
                            Log::error('Failed to record store visit: ' . $e->getMessage());
                        }

                    }

                } else {

                    $cacheManager = (new CacheService(CacheName::STORE_VISIT))->append('guest')->append($sessionId)->append($store->id);

                    if (!$cacheManager->has()) {

                        try {

                            StoreVisitor::updateOrCreate(
                                [
                                    'store_id' => $store->id,
                                    'guest_id' => $sessionId,
                                    'user_id' => null
                                ],
                                [
                                    'id' => Str::uuid(),
                                    'last_visited_at' => now()
                                ]
                            );

                            $cacheManager->put(true, now()->addHour());

                        } catch (\Exception $e) {
                            Log::error('Failed to record store visit: ' . $e->getMessage());
                        }

                    }

                }

            }

        }

        return $response;
    }
}
