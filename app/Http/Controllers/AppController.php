<?php

namespace App\Http\Controllers;

use App\Models\Store;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

class AppController extends Controller
{
    public function render(Request $request, $any = '')
    {
        $segments = explode('/', trim($any, '/'));

        $meta = [
            'type' => 'website', // Default type for non-specific pages
            'url' => config('app.url'),
            'title' => config('app.name'),
            'description' => 'Welcome to '.config('app.name'),
            'image' => url('/images/logo-black-transparent.png'), // Default image
        ];

        // Check if the request is for a product page (e.g., /{alias}/products/{product_id})
        if (count($segments) === 3 && $segments[1] === 'products') {

            $alias = $segments[0];
            $productId = $segments[2];

            $store = Store::where('alias', $alias)->with(['logo'])->first();

            if($store) {

                $product = Product::where('id', $productId)
                    ->where('store_id', $store->id)
                    ->with(['photo'])
                    ->first();

                if($product) {

                    $baseUrl = config('app.url');
                    $productUrl = "{$baseUrl}/{$store->alias}/products/{$product->id}";
                    $image = $product->photo?->path ?? $store->seoImage?->path ?? $store->logo?->path ?? url('/images/logo-black-transparent.png');

                    $meta = [
                        'type' => 'product', // Specific type for product pages
                        'url' => $productUrl,
                        'image' => url($image),
                        'price' => $product->price,
                        'sku' => $product->sku ?? '',
                        'currency' => $product->currency,
                        'title' => "{$product->name} - {$store->name}",
                        'keywords' => "{$product->name}, {$store->name}, e-commerce, store, shop",
                        'availability' => $product->stock_quantity > 0 ? 'in_stock' : 'out_of_stock',
                        'description' => $product->description ?? "Check out this item at {$store->name}",
                    ];

                }

            }

        }elseif (count($segments) === 1 && $segments[0]) {

            // Assume this is a storefront page (e.g., /<alias>)
            $alias = $segments[0];
            $store = Store::where('alias', $alias)->with(['logo', 'seoImage'])->firstOrFail();

            $baseUrl = config('app.url');
            $storeUrl = "{$baseUrl}/{$store->alias}";
            $keywords = $store->seo_keywords ? Arr::join($store->seo_keywords, ',') : '';
            $image = $store->seoImage?->path ?? $store->logo?->path ?? url('/images/logo-black-transparent.png');

            $meta = [
                'image' => $image,
                'url' => $storeUrl,
                'type' => 'website',
                'keywords' => $keywords,
                'meta_pixel_id' => $store->meta_pixel_id,
                'title' => $store->seo_title ?? $store->name,
                'tiktok_pixel_id' => $store->tiktok_pixel_id,
                'google_analytics_id' => $store->google_analytics_id,
                'alias' => $store->alias, // Manifest link - For Progressive Web App (PWA)
                'description' => $store->seo_description ?? ($store->description ?? "Welcome to {$store->name}"),
            ];

        }

        // Pass meta to the Blade view
        return view('render', compact('meta'));
    }
}
