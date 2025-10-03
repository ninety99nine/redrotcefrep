<?php

namespace App\Http\Middleware;

use Closure;
use App\Models\Store;
use App\Models\Domain;
use App\Enums\DomainStatus;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ResolveStoreByDomain
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
        $host = $request->getHost();
        $appUrlHost = parse_url(config('app.url'), PHP_URL_HOST);

        // Skip domain resolution if the host matches the APP_URL host
        if ($host === $appUrlHost) {
            return $next($request);
        }

        // Find a connected domain matching the request host
        $domain = Domain::where('name', $host)->where('status', DomainStatus::CONNECTED->value)->first();

        if ($domain) {

            $store = Store::where('id', $domain->store_id)
                ->with(['logo', 'seoImage'])
                ->first();

            if ($store) {

                // Bind the store to the request for route model binding
                $request->merge(['store' => $store]);
                return $next($request);

            }

        }

        // If no domain or store is found, return a 404 response
        abort(404, 'Store not found for this domain.');
    }
}
