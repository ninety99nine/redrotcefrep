<?php

namespace App\Services;

use Exception;
use Carbon\Carbon;
use App\Models\Tag;
use App\Models\User;
use App\Models\Role;
use App\Models\Store;
use App\Enums\Platform;
use App\Models\Category;
use App\Enums\Association;
use App\Models\Permission;
use Illuminate\Support\Str;
use App\Enums\InsightPeriod;
use Illuminate\Http\Response;
use App\Enums\InsightCategory;
use App\Enums\PaymentMethodType;
use App\Enums\PricingPlanType;
use App\Enums\UploadFolderName;
use Illuminate\Support\Facades\DB;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use App\Http\Resources\StoreResource;
use App\Http\Resources\StoreResources;
use App\Models\PricingPlan;

class StoreService extends BaseService
{
    /**
     * Show stores.
     *
     * @param array $data
     * @return StoreResources|array
     */
    public function showStores(array $data): StoreResources|array
    {
        /** @var User $user */
        $user = Auth::user();
        $association = isset($data['association']) ? Association::tryFrom($data['association']) : null;

        if ($association === Association::SUPER_ADMIN) {
            $query = Store::query()->latest();
        } else if ($association === Association::SHOPPER) {
            $query = Store::query()->latest();
        } elseif ($association === Association::FOLLOWER) {
            $query = $user->followedStores()->when(!request()->has('_sort'), fn($q) => $q->latest());
        } elseif ($association === Association::TEAM_MEMBER) {
            $query = $user->stores()->when(!request()->has('_sort'), fn($q) => $q->latest());
        } elseif ($association === null || $association === Association::RECENT_VISITOR) {
            $query = $user->visitedStores();
            if (!request()->has('_sort')) $query = $query->orderByPivot('last_visited_at', 'desc');
        }

        return $this->setQuery($query)->getOutput();
    }

    /**
     * Create store.
     *
     * @param array $data
     * @return array
     * @throws Exception
     */
    public function createStore(array $data): array
    {
        /** @var User $user */
        $user = Auth::user();

        $store = $user->stores()->create($data);

        // Create default tags
        $defaultTags = ['popular', 'new'];
        foreach ($defaultTags as $tagName) {
            Tag::firstOrCreate([
                'name' => $tagName,
                'store_id' => $store->id,
            ]);
        }

        // Create default category
        Category::firstOrCreate([
            'name' => 'General',
            'store_id' => $store->id
        ]);

        // Create roles
        $adminRole = Role::create(['name' => 'admin', 'store_id' => $store->id, 'guard_name' => 'sanctum']);
        $staffRole = Role::create(['name' => 'staff', 'store_id' => $store->id, 'guard_name' => 'sanctum']);

        $permissions = [
            'manage store',
            'view orders', 'manage orders',
            'view products', 'manage products',
            'view customers', 'manage customers',
            'view promotions', 'manage promotions',
            'view team members', 'manage team members'
        ];

        // Create permissions
        foreach ($permissions as $permissionName) {

            Permission::firstOrCreate([
                'name' => $permissionName,
                'guard_name' => 'sanctum'
            ]);

        }

        $adminPermissions = $permissions; // Grant all permissions
        $staffPermissions = array_diff($permissions, ['manage store', 'manage team members']); // Grant all except these permissions

        $adminRole->syncPermissions($adminPermissions);
        $staffRole->syncPermissions($staffPermissions);

        $user->assignRole($adminRole);

        //  Follow store
        $user->followedStores()->attach($store->id);

        //  Capture store recent visit
        $user->visitedStores()->syncWithoutDetaching([$store->id => [
            'id' => Str::uuid(),
            'last_visited_at' => now()
        ]]);

        $isValidUssdRequest = (new UssdService)->isValidUssdRequest();

        if($isValidUssdRequest) {

            $pricingPlan = PricingPlan::where('type', PricingPlanType::STORE_SUBSCRIPTION->value)->supportsUssd()->active()->orderBy('price')->first();

            //  Creaate trial store subscription
            (new PricingPlanService)->payPricingPlan($pricingPlan, [
                'store' => $store,
                'payment_method_type' => PaymentMethodType::ORANGE_AIRTIME->value
            ]);

        }

        //  Forget cache
        (new UssdService)->cacheManager($user)->forget();

        // Create store logo if provided
        if (isset($data['logo']) && !empty($data['logo'])) {

            (new MediaFileService)->createMediaFile([
                'file' => $data['logo'],
                'mediable_type' => 'store',
                'mediable_id' => $store->id,
                'upload_folder_name' => UploadFolderName::STORE_LOGO->value
            ]);

        }

        return $this->showCreatedResource($store);
    }

    /**
     * Show store by alias.
     *
     * @param string $alias
     * @return StoreResource
     */
    public function showStoreByAlias(string $alias): StoreResource
    {
        $store = Store::where('id', $alias)->orWhere('alias', $alias)
                    ->with($this->getRequestRelationships())
                    ->withCount($this->getRequestCountableRelationships())
                    ->firstOrFail();

        return $this->showResource($store);
    }

    /**
     * Delete Stores.
     *
     * @param array $storeIds
     * @return array
     * @throws Exception
     */
    public function deleteStores(array $storeIds): array
    {
        $stores = Store::whereIn('id', $storeIds)->with(['mediaFiles'])->get();

        if($totalStores = $stores->count()) {

            foreach ($stores as $store) {

                $this->deleteStore($store, false);

            }

            /** @var User $user */
            $user = Auth::user();

            //  Forget cache
            (new UssdService)->cacheManager($user)->forget();

            return ['message' => $totalStores  . ($totalStores  == 1 ? ' Store': ' Stores') . ' deleted'];

        }else{

            throw new Exception('No Stores deleted');

        }
    }

    /**
     * Show store.
     *
     * @param Store $store
     * @return StoreResource
     */
    public function showStore(Store $store): StoreResource
    {
        return $this->showResource($store);
    }

    /**
     * Update store.
     *
     * @param Store $store
     * @param array $data
     * @return array
     */
    public function updateStore(Store $store, array $data): array
    {
        $store->update($data);
        return $this->showUpdatedResource($store);
    }

    /**
     * Delete store.
     *
     * @param Store $store
     * @param boolean $forgetCache
     * @return array
     * @throws Exception
     */
    public function deleteStore(Store $store, $forgetCache = true): array
    {
        $mediaFileService = new MediaFileService;

        foreach ($store->mediaFiles as $mediaFile) {
            $mediaFileService->deleteMediaFile($mediaFile);
        }

        $deleted = $store->delete();

        if($forgetCache) {

            /** @var User $user */
            $user = Auth::user();

            //  Forget cache
            (new UssdService)->cacheManager($user)->forget();

        }

        if ($deleted) {
            return ['message' => 'Store deleted'];
        }else{
            throw new Exception('Store delete unsuccessful');
        }
    }

    /**
     * Follow store.
     *
     * @param Store $store
     * @return array
     * @throws Exception
     */
    public function followStore(Store $store): array
    {
        /** @var User $user */
        $user = Auth::user();

        if (!$user->followedStores()->where('store_id', $store->id)->exists()) {

            $user->followedStores()->attach($store->id);

            //  Forget cache
            (new UssdService)->cacheManager($user)->forget();

        }

        return ['message' => 'Store followed successfully'];
    }

    /**
     * Unfollow store.
     *
     * @param Store $store
     * @return array
     * @throws Exception
     */
    public function unfollowStore(Store $store): array
    {
        /** @var User $user */
        $user = Auth::user();

        if ($user->followedStores()->where('store_id', $store->id)->exists()) {

            $user->followedStores()->detach($store->id);

            //  Forget cache
            (new UssdService)->cacheManager($user)->forget();

        }

        return ['message' => 'Store unfollowed successfully'];
    }

    /**
     * Show store insights.
     *
     * @param Store $store
     * @param array $data
     * @return array
     */
    public function showStoreInsights(Store $store, array $data): array
    {
        $insights = [];
        $platform = $data['platform'] ?? Platform::WEB->value;
        $period = $data['period'] ?? InsightPeriod::TODAY->value;
        $isUssd = Platform::tryFrom($platform) == Platform::USSD;
        $categories = $data['categories'] ?? [InsightCategory::SALES->value];

        $add = function($title, $description, array $categoryInsights) use (&$insights) {
            array_push($insights, [
                'title' => $title,
                'description' => $description,
                'category_insights' => collect($categoryInsights)->map(function($categoryInsight) {
                    return [
                        'name' => $categoryInsight[0],
                        'type' => $categoryInsight[2],
                        'metric' => $categoryInsight[1],
                        'description' => $categoryInsight[3],
                    ];
                })->values()
            ]);
        };

        [$dateRange1, $dateRange2] = match ($period) {
            InsightPeriod::TODAY->value => [Carbon::today(), Carbon::now()],
            InsightPeriod::YESTERDAY->value => [Carbon::yesterday()->startOfDay(), Carbon::yesterday()->endOfDay()],
            InsightPeriod::THIS_WEEK->value => [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()],
            InsightPeriod::THIS_MONTH->value => [Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()],
            InsightPeriod::THIS_YEAR->value => [Carbon::now()->startOfYear(), Carbon::now()->endOfYear()]
        };

        [$periodName, $periodType] = match ($period) {
            InsightPeriod::TODAY->value => ['hour', '%H:00'],
            InsightPeriod::YESTERDAY->value => ['hour', '%H:00'],
            InsightPeriod::THIS_WEEK->value => ['day', '%a'],
            InsightPeriod::THIS_MONTH->value => ['day', '%D'],
            InsightPeriod::THIS_YEAR->value => ['month', '%b']
        };

        $ordersQuery = DB::table('orders')->where('store_id', $store->id);

        if ($dateRange1 && $dateRange2) $ordersQuery->whereBetween('created_at', [$dateRange1, $dateRange2]);

        if (empty($categories) || in_array(InsightCategory::SALES->value, $categories)) {

            $salesByPeriod = $ordersQuery
                ->selectRaw("DATE_FORMAT(created_at, '$periodType') as period, COUNT(*) as total_orders, SUM(grand_total) as total_grand_total")
                ->groupByRaw("DATE_FORMAT(created_at, '$periodType')")
                ->orderByRaw("COUNT(*) DESC")
                ->get();

            $totalOrders = $salesByPeriod->sum('total_orders');
            $totalSales = $salesByPeriod->sum('total_grand_total');
            $avgSalesPerOrder = $totalSales / max($totalOrders, 1);

            $totalSalesByPeriod = $salesByPeriod->pluck('total_grand_total', 'period')->sortDesc();

            if ($totalSalesByPeriod->isEmpty()) {
                $highestSalesDay = $lowestSalesDay = 'N/A';
            } else {
                $highestSalesHour = $totalSalesByPeriod->keys()->first();
                $highestSalesAmount = $totalSalesByPeriod->get($highestSalesHour, 0);

                $lowestSalesHour = $totalSalesByPeriod->keys()->last();
                $lowestSalesAmount = $totalSalesByPeriod->get($lowestSalesHour, 0);

                $highestSalesDay = $highestSalesHour ? "{$highestSalesHour} ({MoneyService::convertToMoneyFormat($highestSalesAmount, $store->getRawOriginal('currency'))->amount_with_currency})" : 'N/A';

                $lowestSalesDays = $totalSalesByPeriod->filter(function ($amount) use ($lowestSalesAmount) {
                    return $amount === $lowestSalesAmount;
                })->keys();

                if ($lowestSalesDays->count() === 1) {
                    $lowestSalesDay = "{$lowestSalesDays->first()} ({MoneyService::convertToMoneyFormat($lowestSalesAmount, $store->getRawOriginal('currency'))->amount_with_currency})";
                } else {
                    $lowestSalesDay = 'N/A';
                }

                if ($highestSalesAmount === $lowestSalesAmount) {
                    $lowestSalesDay = 'N/A';
                }
            }

            $totalSales = MoneyService::convertToMoneyFormat($totalSales, $store->getRawOriginal('currency'))->amount_with_currency;

            $add(
                'Sale Insights',
                'Store performance based on sales',
                [
                    [($isUssd ? 'Sales' : 'Total sales'), $totalSales.' ('. $totalOrders . ($totalOrders == 1 ? ' order' : ' orders') . ')', 'total_sales', 'The total sales revenue generated from orders placed in the store'],
                    [($isUssd ? 'Avg sale per order' : 'Average sale per order'), MoneyService::convertToMoneyFormat($avgSalesPerOrder, $store->getRawOriginal('currency'))->amount_with_currency, 'average_sale_per_order', 'The average sales revenue earned per order based on the total sales divided by the number of orders'],
                    [($isUssd ? "Best $periodName" : "Highest sales $periodName"), $highestSalesDay, 'highest_sale_period', "The $periodName with the highest recorded sales amount"],
                    [($isUssd ? "Worst $periodName" : "Lowest sales $periodName"), $lowestSalesDay, 'lowest_sale_period', "The $periodName with the lowest recorded sales amount"]
                ]
            );

        }

        if (empty($categories) || in_array(InsightCategory::ORDERS->value, $categories)) {

            $ordersByPeriod = $ordersQuery
                ->selectRaw("DATE_FORMAT(created_at, '$periodType') as period, COUNT(*) as total_orders, SUM(grand_total) as total_grand_total")
                ->groupByRaw("DATE_FORMAT(created_at, '$periodType')")
                ->orderByRaw("COUNT(*) DESC")
                ->get();

            $totalOrders = $ordersByPeriod->sum('total_orders');
            $totalSales = $ordersByPeriod->sum('total_grand_total');

            $totalOrdersByPeriod = $ordersByPeriod->pluck('total_orders', 'period')->sortDesc();

            if ($ordersByPeriod->isEmpty()) {
                $mostOrderDay = $leastOrderDay = 'N/A';
            } else {
                $mostOrderPeriod = $totalOrdersByPeriod->keys()->first();
                $mostOrderCount = $totalOrdersByPeriod->values()->first();

                $leastOrderPeriod = $totalOrdersByPeriod->keys()->last();
                $leastOrderCount = $totalOrdersByPeriod->values()->last();

                $mostOrderDay = "{$mostOrderPeriod} ({$mostOrderCount} " . ($mostOrderCount == 1 ? 'order' : 'orders') . ")";

                $leastOrderPeriods = $ordersByPeriod->filter(function ($order) use ($leastOrderCount) {
                    return $order->total_orders === $leastOrderCount;
                })->pluck('period');

                if ($leastOrderPeriods->count() === 1) {
                    $leastOrderDay = "{$leastOrderPeriods->first()} ({$leastOrderCount} " . ($leastOrderCount == 1 ? 'order' : 'orders') . ")";
                } else {
                    $leastOrderDay = 'N/A';
                }
            }

            $totalSales = MoneyService::convertToMoneyFormat($totalSales, $store->getRawOriginal('currency'))->amount_with_currency;

            $add(
                'Order Insights',
                'Store performance based on orders',
                [
                    [($isUssd ? 'Orders' : 'Total orders'), "{$totalOrders} ({$totalSales})", 'total_orders', 'The total number of orders placed, along with the total sales revenue generated from those orders'],
                    ['Most orders', $mostOrderDay, 'most_orders', "The $periodName with the highest number of orders placed"],
                    ['Least orders', $leastOrderDay, 'least_orders', "The $periodName with the lowest number of orders placed"],
                ]
            );

        }

        if (empty($categories) || in_array(InsightCategory::PRODUCTS->value, $categories)) {

            $productsBySales = DB::table('order_products')
                ->selectRaw("
                    product_id,
                    products.name as product_name,
                    SUM(quantity) as total_quantity,
                    SUM(grand_total) as total_revenue,
                    SUM(CASE WHEN is_cancelled = 1 THEN quantity ELSE 0 END) as cancelled_quantity,
                    SUM(CASE WHEN is_cancelled = 1 THEN grand_total ELSE 0 END) as cancelled_revenue,
                    SUM(order_products.unit_sale_discount) as total_discount
                ")
                ->join('products', 'order_products.product_id', '=', 'products.id')
                ->where('order_products.store_id', $store->id)
                ->whereBetween('order_products.created_at', [$dateRange1, $dateRange2])
                ->groupBy('product_id')
                ->orderBy('total_revenue', 'desc')
                ->orderBy('total_quantity', 'desc')
                ->get();

            // Top-selling product
            $topSellingProduct = $productsBySales->first();
            $topSelling = $topSellingProduct
                ? "{$topSellingProduct->product_name} ({$topSellingProduct->total_quantity} units, " .
                    MoneyService::convertToMoneyFormat($topSellingProduct->total_revenue, $store->getRawOriginal('currency'))->amount_with_currency . ")"
                : 'N/A';

            // Least-selling product
            $leastSellingProduct = $productsBySales->last();
            if ($leastSellingProduct) {
                if ($topSellingProduct->product_id != $leastSellingProduct->product_id) {
                    $matchingLeastSellingProducts = $productsBySales->filter(function ($productBySale) use ($leastSellingProduct) {
                        return $productBySale->total_revenue === $leastSellingProduct->total_revenue &&
                                $productBySale->total_quantity === $leastSellingProduct->total_quantity;
                    });

                    if ($matchingLeastSellingProducts->count() === 1) {
                        $leastSelling = "{$leastSellingProduct->product_name} ({$leastSellingProduct->total_quantity} units, " .
                            MoneyService::convertToMoneyFormat($leastSellingProduct->total_revenue, $store->getRawOriginal('currency'))->amount_with_currency . ")";
                    } else {
                        $leastSelling = 'N/A';
                    }
                } else {
                    $leastSelling = 'N/A';
                }
            } else {
                $leastSelling = 'N/A';
            }

            // Total and canceled quantities, revenue, and discount
            $totalQuantity = $productsBySales->sum('total_quantity');
            $totalProductRevenue = $productsBySales->sum('total_revenue');
            $totalCancelledQuantity = $productsBySales->sum('cancelled_quantity');
            $totalCancelledRevenue = $productsBySales->sum('cancelled_revenue');
            $totalDiscount = $productsBySales->sum('total_discount');

            // Average revenue per product
            $avgRevenuePerProduct = $totalQuantity > 0 ? $totalProductRevenue / $totalQuantity : 0;

            $avgRevenuePerProductFormatted = MoneyService::convertToMoneyFormat($avgRevenuePerProduct, $store->getRawOriginal('currency'))->amount_with_currency;
            $totalProductRevenueFormatted = MoneyService::convertToMoneyFormat($totalProductRevenue, $store->getRawOriginal('currency'))->amount_with_currency;
            $totalCancelledRevenueFormatted = MoneyService::convertToMoneyFormat($totalCancelledRevenue, $store->getRawOriginal('currency'))->amount_with_currency;
            $totalDiscountFormatted = MoneyService::convertToMoneyFormat($totalDiscount, $store->getRawOriginal('currency'))->amount_with_currency;

            $add(
                'Product Insights',
                'Store performance based on products',
                [
                    ['Top-selling', $topSelling, 'top_selling', 'The product that has generated the highest quantity of sales'],
                    ['Least-selling', $leastSelling, 'least_selling', 'The product that has generated the lowest quantity of sales'],
                    ['Products sold', $totalQuantity . ' (' . $totalProductRevenueFormatted . ')', 'products_sold', 'The total quantity of products sold, along with the total revenue generated from their sales'],
                    ['Products cancelled', $totalCancelledQuantity . ' (' . $totalCancelledRevenueFormatted . ')', 'products_cancelled', 'The total number of products cancelled on placed orders, along with the total revenue associated with those cancellations'],
                    ['Offered discounts', $totalDiscountFormatted, 'offered_discounts', 'The total value of discounts provided across all products and orders'],
                    [$isUssd ? 'Avg revenue per product' : 'Average revenue per product', $avgRevenuePerProductFormatted, 'average_revenue_per_product', 'The average amount of revenue earned per product sold']
                ]
            );
        }

        if (empty($categories) || in_array(InsightCategory::CUSTOMERS->value, $categories)) {

            $customersData = DB::table('customers')
                ->selectRaw("
                    customers.id as customer_id,
                    COUNT(orders.id) as total_orders,
                    SUM(orders.grand_total) as total_spend
                ")
                ->leftJoin('orders', 'customers.id', '=', 'orders.customer_id')
                ->where('orders.store_id', $store->id)
                ->whereBetween('orders.created_at', [$dateRange1, $dateRange2])
                ->groupBy('customer_id')
                ->get();

            // Total customers
            $totalCustomers = $customersData->count();

            // New and return customers
            $newCustomers = $customersData->filter(fn($record) => $record->total_orders == 1)->count();
            $returnCustomers = $customersData->filter(fn($record) => $record->total_orders > 1)->count();

            // Retention Rate
            $retentionRate = $totalCustomers ? ($returnCustomers / $totalCustomers) * 100 : 0;

            // Revenue per Customer
            $totalRevenue = $customersData->sum('total_spend');
            $revenuePerCustomer = $totalCustomers ? $totalRevenue / $totalCustomers : 0;
            $revenuePerCustomer = MoneyService::convertToMoneyFormat($revenuePerCustomer, $store->getRawOriginal('currency'))->amount_with_currency;

            // Determine previous date range based on the period
            [$previousDateRange1, $previousDateRange2] = match ($period) {
                InsightPeriod::TODAY->value => [$dateRange1->subDay(), $dateRange2->subDay()],
                InsightPeriod::YESTERDAY->value => [$dateRange1->subDays(2), $dateRange2->subDay(2)],
                InsightPeriod::THIS_WEEK->value => [$dateRange1->subWeek(), $dateRange2->subWeek()],
                InsightPeriod::THIS_MONTH->value => [$dateRange1->subMonth(), $dateRange2->subMonth()],
                InsightPeriod::THIS_YEAR->value => [$dateRange1->subYear(), $dateRange2->subYear()],
            };

            // Get the number of customers in the previous period
            $previousPeriodCustomers = DB::table('customers')
                ->leftJoin('orders', 'customers.id', '=', 'orders.customer_id')
                ->where('orders.store_id', $store->id)
                ->whereBetween('orders.created_at', [$previousDateRange1, $previousDateRange2])
                ->count();

            // Calculate Customer Growth Rate
            $customerGrowthRate = 0;

            if ($previousPeriodCustomers == 0 && $totalCustomers > 0) {
                $customerGrowthRate = 100;
            } elseif ($previousPeriodCustomers > 0) {
                $customerGrowthRate = (($totalCustomers - $previousPeriodCustomers) / $previousPeriodCustomers) * 100;
            }

            $add(
                'Customer Insights',
                'Store performance based on customers',
                [
                    ['Total customers', $totalCustomers, 'total_customers', 'The total number of unique customers on this store'],
                    ['New customers', $newCustomers, 'new_customers', 'The number of customers who have only placed one order'],
                    ['Return customers', $returnCustomers, 'return_customers', 'The number of customers who have placed more than one order'],
                    ['Retention Rate', $retentionRate . '%', 'retention_rate', 'The percentage of customers who made repeat purchases, indicating customer loyalty'],
                    ['Revenue per Customer', $revenuePerCustomer, 'revenue_per_customer', 'The average revenue generated from each customer, calculated as total sales divided by the total number of customers'],
                    ['Customer Growth Rate', round($customerGrowthRate, 1) . '% (' . ($customerGrowthRate > 0 ? 'increased' : ($customerGrowthRate == 0 ? 'no change' : 'decreased')) . ')', 'customer_growth_rate', 'The rate at which the customer base has grown or decreased, expressed as a percentage']
                ]
            );

        }

        if (empty($categories) || in_array(InsightCategory::OPERATIONS->value, $categories)) {


        }

        return [
            'insights' => $insights
        ];
    }

    /**
     * Show store QR code.
     *
     * @param Store $store
     * @return Response
     */
    public function showStoreQrCode(Store $store): Response
    {
        $response = Http::get($store->qr_code_file_path);

        return response($response->body(), 200)
            ->header('Content-Type', 'image/png')
            ->header('Content-Disposition', 'inline; filename="' . $store->name . ' QR Code.png"');
    }

    /**
     * Show store QR code preview.
     *
     * @param Store $store
     * @return View
     */
    public function showStoreQrCodePreview(Store $store): View
    {
        return view('qr-code-image-previews.store-qr-code-image-preview', ['store' => $store]);
    }
}
