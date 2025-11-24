<?php

namespace App\Http\Controllers;

use App\Models\Store;
use App\Models\Product;
use Illuminate\Support\Arr;
use Illuminate\Http\Request;

class AppController extends Controller
{
    /**
     * Render the application.
     *
     * @param Request $request
     * @param string $any
     * @return \Illuminate\View\View
     */
    public function render(Request $request, $any = '')
    {
        $segments = explode('/', trim($any, '/'));
        $baseUrl = $request->getSchemeAndHttpHost();

        // Check if store is resolved via middleware (ResolveStoreByDomain)
        $store = $request->get('store');

        if (!$store) {
            // Fallback to alias-based store resolution
            if (count($segments) >= 1 && $segments[0]) {
                $alias = $segments[0];
                $store = Store::where('alias', $alias)->with(['logo', 'seoImage'])->first();
            }
        }

        // Handle product page
        if ($store && count($segments) === 2 && $segments[0] === 'products') {

            $productId = $segments[1];

            $product = Product::where('id', $productId)
                ->where('store_id', $store->id)
                ->with(['photo'])
                ->first();

            if ($product) {

                $productUrl = "{$baseUrl}/products/{$product->id}";
                $image = $product->photo?->path ?? $store->seoImage?->path ?? $store->logo?->path ?? url('/images/logo-black-transparent.png');

                $meta = [
                    'type' => 'product',
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

        // Handle storefront page
        } elseif ($store) {

            $storeUrl = $baseUrl;
            $keywords = $store->seo_keywords ? Arr::join($store->seo_keywords, ',') : '';
            $image = $store->seoImage?->path ?? $store->logo?->path ?? url('/images/logo-black-transparent.png');

            $meta = [
                'image' => $image,
                'url' => $storeUrl,
                'type' => 'website',
                'keywords' => $keywords,
                'alias' => $store->alias,
                'store_id' => $store->id,
                'meta_pixel_id' => $store->meta_pixel_id,
                'title' => $store->seo_title ?? $store->name,
                'tiktok_pixel_id' => $store->tiktok_pixel_id,
                'google_analytics_id' => $store->google_analytics_id,
                'description' => $store->seo_description ?? ($store->description ?? "Welcome to {$store->name}"),
            ];

        // Handle any other page
        }else{

            $meta = [
                'type' => 'website',
                'title' => config('app.name'),
                'url' => rtrim(config('app.url'), '/'),
                'description' => 'Welcome to '.config('app.name'),
                'image' => url('/images/logo-black-transparent.png')
            ];

        }

        // Pass meta to the Blade view
        return view('render', compact('meta'));
    }
}
