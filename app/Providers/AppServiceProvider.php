<?php

namespace App\Providers;

use App\Models\Order;
use App\Models\Store;
use App\Models\Address;
use App\Models\Product;
use App\Models\Promotion;
use App\Models\MediaFile;
use App\Models\StoreQuota;
use App\Models\PricingPlan;
use App\Models\AiAssistant;
use App\Models\Transaction;
use App\Models\Subscription;
use App\Models\PaymentMethod;
use App\Models\DeliveryMethod;
use App\Models\DeliveryAddress;
use App\Observers\StoreObserver;
use App\Models\StorePaymentMethod;
use App\Listeners\RoleEventListener;
use App\Models\AiAssistantTokenUsage;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Event;
use Illuminate\Foundation\Http\Kernel;
use Illuminate\Support\ServiceProvider;
use App\Http\Middleware\StorePermission;
use App\Models\OrderComment;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Routing\Middleware\SubstituteBindings;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Relation::enforceMorphMap([
            'user' => 'App\Models\User',
            'order' => 'App\Models\Order',
            'store' => 'App\Models\Store',
            'product' => 'App\Models\Product',
            'transaction' => 'App\Models\Transaction',
            'pricing plan' => 'App\Models\PricingPlan',
            'order comment' => 'App\Models\OrderComment'
        ]);

        JsonResource::withoutWrapping();

        $this->explicitRouteModelBiniding();

        //  Events
        Event::subscribe(RoleEventListener::class);

        //  Observers
        Store::observe(StoreObserver::class);

        /**
         *  Reference: https://spatie.be/docs/laravel-permission/v6/basic-usage/teams-permissions
         *
         *  @var Kernel $kernel
         */
        $kernel = app()->make(Kernel::class);

        $kernel->addToMiddlewarePriorityBefore(
            SubstituteBindings::class,
            StorePermission::class
        );
    }

    /**
     *  Customize how we resolve route model bindings.
     *
     *  Reference: https://laravel.com/docs/12.x/routing#customizing-the-resolution-logic
     */
    private function explicitRouteModelBiniding()
    {
        // Bind Store model
        Route::bind('store', function ($value) {
            $allowedRoutes = ['show.store'];
            return $this->applyEagerLoading(Store::query(), $allowedRoutes)->findOrFail($value);
        });

        // Bind StoreQuota model
        Route::bind('storeQuota', function ($value) {
            $allowedRoutes = ['show.store.quota'];
            return $this->applyEagerLoading(StoreQuota::query(), $allowedRoutes)->findOrFail($value);
        });

        // Bind StorePaymentMethod model
        Route::bind('storePaymentMethod', function ($value) {
            $allowedRoutes = ['show.store.payment.method'];
            return $this->applyEagerLoading(StorePaymentMethod::query(), $allowedRoutes)->findOrFail($value);
        });

        // Bind Order model
        Route::bind('order', function ($value) {
            $allowedRoutes = ['show.order'];
            return $this->applyEagerLoading(Order::query(), $allowedRoutes)->findOrFail($value);
        });

        // Bind OrderComment model
        Route::bind('orderComment', function ($value) {
            $allowedRoutes = ['show.order.comment'];
            return $this->applyEagerLoading(OrderComment::query(), $allowedRoutes)->findOrFail($value);
        });

        // Bind Address model
        Route::bind('address', function ($value) {
            $allowedRoutes = ['show.address'];
            return $this->applyEagerLoading(Address::query(), $allowedRoutes)->findOrFail($value);
        });

        Route::bind('deliveryAddress', function ($value) {
            $allowedRoutes = ['show.delivery.address'];
            return $this->applyEagerLoading(DeliveryAddress::query(), $allowedRoutes)->findOrFail($value);
        });

        // Bind Product model
        Route::bind('product', function ($value) {
            $allowedRoutes = ['show.product'];
            return $this->applyEagerLoading(Product::query(), $allowedRoutes)->findOrFail($value);
        });

        // Bind Promotion model
        Route::bind('promotion', function ($value) {
            $allowedRoutes = ['show.promotion'];
            return $this->applyEagerLoading(Promotion::query(), $allowedRoutes)->findOrFail($value);
        });

        // Bind Transaction model
        Route::bind('transaction', function ($value) {
            $allowedRoutes = ['show.transaction'];
            return $this->applyEagerLoading(Transaction::query(), $allowedRoutes)->findOrFail($value);
        });

        // Bind Subscription model
        Route::bind('subscription', function ($value) {
            $allowedRoutes = ['show.subscription'];
            return $this->applyEagerLoading(Subscription::query(), $allowedRoutes)->findOrFail($value);
        });

        // Bind MediaFile model
        Route::bind('mediaFile', function ($value) {
            $allowedRoutes = ['show.media.file'];
            return $this->applyEagerLoading(MediaFile::query(), $allowedRoutes)->findOrFail($value);
        });

        // Bind PaymentMethod model
        Route::bind('paymentMethod', function ($value) {
            $allowedRoutes = ['show.payment.method'];
            return $this->applyEagerLoading(PaymentMethod::query(), $allowedRoutes)->findOrFail($value);
        });

        // Bind DeliveryMethod model
        Route::bind('deliveryMethod', function ($value) {
            $allowedRoutes = ['show.delivery.method'];
            return $this->applyEagerLoading(DeliveryMethod::query(), $allowedRoutes)->findOrFail($value);
        });

        // Bind PricingPlan model
        Route::bind('pricingPlan', function ($value) {
            $allowedRoutes = ['show.pricing.plan'];
            return $this->applyEagerLoading(PricingPlan::query(), $allowedRoutes)->findOrFail($value);
        });

        // Bind AiAssistant model
        Route::bind('aiAssistant', function ($value) {
            $allowedRoutes = ['show.ai.assistant'];
            return $this->applyEagerLoading(AiAssistant::query(), $allowedRoutes)->findOrFail($value);
        });

        // Bind AiAssistantTokenUsage model
        Route::bind('aiAssistantTokenUsage', function ($value) {
            $allowedRoutes = ['show.ai.assistant.token.usage'];
            return $this->applyEagerLoading(AiAssistantTokenUsage::query(), $allowedRoutes)->findOrFail($value);
        });
    }

    /**
     * Apply eager loading to a query based on request parameters, only for allowed routes.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param array $allowedRoutes Route names where eager loading should apply
     * @return \Illuminate\Database\Eloquent\Builder
     */
    private function applyEagerLoading($query, array $allowedRoutes = [])
    {
        // Skip eager loading if current route is not in allowed routes
        if (!in_array(Route::currentRouteName(), $allowedRoutes)) {
            return $query;
        }

        $relationships = array_filter(array_map('trim', explode(',', request()->input('_relationships', ''))));
        $countableRelationships = array_filter(array_map('trim', explode(',', request()->input('_countable_relationships', ''))));

        if (!empty($relationships)) {
            $query->with($relationships);
        }

        if (!empty($countableRelationships)) {
            $query->withCount($countableRelationships);
        }

        return $query;
    }
}
