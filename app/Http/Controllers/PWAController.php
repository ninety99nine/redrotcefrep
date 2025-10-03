<?php

namespace App\Http\Controllers;

use App\Models\Store;
use Illuminate\Http\Request;

class PWAController extends Controller
{
    /**
     * Generate PWA manifest for a store.
     *
     * @param Request $request
     * @param string|null $alias
     * @return \Illuminate\Http\JsonResponse
     */
    public function manifest(Request $request, ?string $alias = null)
    {
        $store = $request->get('store');

        if (!$store && $alias) {
            $store = Store::where('alias', $alias)->with(['logo'])->firstOrFail();
        }

        if (!$store) {
            abort(404, 'Store not found.');
        }

        $baseUrl = $request->getSchemeAndHttpHost();

        $manifest = [
            'name' => $store->name,
            'short_name' => $store->name,
            'start_url' => $baseUrl,
            'display' => 'standalone',
            'background_color' => '#ffffff',
            'theme_color' => '#1E40AF',
            'orientation' => 'portrait-primary',
            'icons' => [
                [
                    'src' => $store->logo?->path ?? '/images/logo-192x192.png',
                    'sizes' => '192x192',
                    'type' => 'image/png'
                ],
                [
                    'src' => $store->logo?->path ?? '/images/logo-512x512.png',
                    'sizes' => '512x512',
                    'type' => 'image/png'
                ]
            ]
        ];

        return response()->json($manifest);
    }
}
