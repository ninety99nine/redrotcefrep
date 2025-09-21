<?php

namespace App\Http\Controllers;

use App\Models\Store;

class PWAController extends Controller
{
    public function manifest($alias)
    {
        $store = Store::where('alias', $alias)->with(['logo'])->firstOrFail();

        $manifest = [
            'name' => $store->name,
            'short_name' => $store->name,
            'start_url' => "/{$store->alias}",
            'display' => 'standalone',
            'background_color' => '#ffffff',
            'theme_color' => '#1E40AF',
            'orientation' => 'portrait-primary',
            'icons' => [
                [
                    'src' => $store->logo->path ?? '/images/logo-192x192.png',
                    'sizes' => '192x192',
                    'type' => 'image/png'
                ],
                [
                    'src' => $store->logo->path ?? '/images/logo-512x512.png',
                    'sizes' => '512x512',
                    'type' => 'image/png'
                ]
            ]
        ];

        return response()->json($manifest);
    }
}
