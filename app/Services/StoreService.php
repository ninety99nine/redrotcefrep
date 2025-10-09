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
use App\Models\PricingPlan;
use Illuminate\Support\Str;
use App\Enums\InsightPeriod;
use Illuminate\Http\Response;
use App\Enums\InsightCategory;
use App\Enums\PricingPlanType;
use App\Enums\UploadFolderName;
use App\Enums\PaymentMethodType;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use App\Http\Resources\StoreResource;
use App\Http\Resources\StoreResources;

class StoreService extends BaseService
{
    public static $defaultCategoryName = 'General';

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
            'name' => self::$defaultCategoryName,
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

        //  Build the store design cards
        $this->buildStoreDesignCards($store);

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
        $categories = $data['categories'] ?? [InsightCategory::SALES->value];
        $isUssd = Platform::tryFrom($platform) === Platform::USSD;

        // Validate period to prevent undefined variable errors
        if (!InsightPeriod::tryFrom($period)) {
            $period = InsightPeriod::TODAY->value; // Default to TODAY
        }

        // Define date ranges
        [$dateRange1, $dateRange2] = match ($period) {
            InsightPeriod::TODAY->value => [Carbon::today(), Carbon::now()],
            InsightPeriod::YESTERDAY->value => [Carbon::yesterday()->startOfDay(), Carbon::yesterday()->endOfDay()],
            InsightPeriod::THIS_WEEK->value => [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()],
            InsightPeriod::THIS_MONTH->value => [Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()],
            InsightPeriod::THIS_YEAR->value => [Carbon::now()->startOfYear(), Carbon::now()->endOfYear()],
            default => [Carbon::today(), Carbon::now()] // Fallback
        };

        // Define period formatting
        [$periodName, $periodType] = match ($period) {
            InsightPeriod::TODAY->value => ['hour', '%H:00'],
            InsightPeriod::YESTERDAY->value => ['hour', '%H:00'],
            InsightPeriod::THIS_WEEK->value => ['day', '%a'],
            InsightPeriod::THIS_MONTH->value => ['day', '%d'],
            InsightPeriod::THIS_YEAR->value => ['month', '%b'],
            default => ['hour', '%H:00'] // Fallback
        };

        // Helper function to add insights
        $add = function ($title, $description, array $categoryInsights) use (&$insights) {
            $insights[] = [
                'title' => $title,
                'description' => $description,
                'category_insights' => collect($categoryInsights)->map(function ($categoryInsight) {
                    return [
                        'name' => $categoryInsight[0],
                        'type' => $categoryInsight[2],
                        'metric' => $categoryInsight[1],
                        'description' => $categoryInsight[3],
                    ];
                })->values()->all()
            ];
        };

        // Base orders query (reused for SALES and ORDERS)
        $ordersQuery = DB::table('orders')
            ->where('store_id', $store->id)
            ->whereBetween('created_at', [$dateRange1, $dateRange2]);

        try {
            // Shared query for SALES and ORDERS
            $ordersByPeriod = $ordersQuery
                ->selectRaw("DATE_FORMAT(created_at, ?) as period, COUNT(*) as total_orders, SUM(grand_total) as total_grand_total", [$periodType])
                ->groupByRaw('period')
                ->orderByRaw('total_orders DESC')
                ->get();

            if (empty($categories) || in_array(InsightCategory::SALES->value, $categories)) {
                $totalOrders = $ordersByPeriod->sum('total_orders');
                $totalSales = $ordersByPeriod->sum('total_grand_total');
                $avgSalesPerOrder = $totalOrders > 0 ? $totalSales / $totalOrders : 0;

                $totalSalesByPeriod = $ordersByPeriod->pluck('total_grand_total', 'period')->sortByDesc(null);

                $highestSalesDay = 'N/A';
                $lowestSalesDay = 'N/A';

                if ($totalSalesByPeriod->isNotEmpty()) {
                    $highestSalesPeriod = $totalSalesByPeriod->keys()->first();
                    $highestSalesAmount = $totalSalesByPeriod->get($highestSalesPeriod, 0);

                    $lowestSalesPeriod = $totalSalesByPeriod->keys()->last();
                    $lowestSalesAmount = $totalSalesByPeriod->get($lowestSalesPeriod, 0);

                    // Fixed: Properly evaluate MoneyService::convertToMoneyFormat
                    $highestSalesDay = "{$highestSalesPeriod} (" . MoneyService::convertToMoneyFormat($highestSalesAmount, $store->getRawOriginal('currency'))->amount_with_currency . ")";

                    $lowestSalesDays = $totalSalesByPeriod->filter(fn($amount) => $amount === $lowestSalesAmount)->keys();

                    if ($lowestSalesDays->count() === 1 && $highestSalesAmount !== $lowestSalesAmount) {
                        $lowestSalesDay = "{$lowestSalesDays->first()} (" . MoneyService::convertToMoneyFormat($lowestSalesAmount, $store->getRawOriginal('currency'))->amount_with_currency . ")";
                    }
                }

                $totalSalesFormatted = MoneyService::convertToMoneyFormat($totalSales, $store->getRawOriginal('currency'))->amount_with_currency;

                $add(
                    'Sale Insights',
                    'Store performance based on sales',
                    [
                        [$isUssd ? 'Sales' : 'Total sales', "{$totalSalesFormatted} ({$totalOrders} " . ($totalOrders == 1 ? 'order' : 'orders') . ")", 'total_sales', 'The total sales revenue generated from orders placed in the store'],
                        [$isUssd ? 'Avg sale per order' : 'Average sale per order', MoneyService::convertToMoneyFormat($avgSalesPerOrder, $store->getRawOriginal('currency'))->amount_with_currency, 'average_sale_per_order', 'The average sales revenue earned per order based on the total sales divided by the number of orders'],
                        [$isUssd ? "Best $periodName" : "Highest sales $periodName", $highestSalesDay, 'highest_sale_period', "The $periodName with the highest recorded sales amount"],
                        [$isUssd ? "Worst $periodName" : "Lowest sales $periodName", $lowestSalesDay, 'lowest_sale_period', "The $periodName with the lowest recorded sales amount"]
                    ]
                );
            }

            if (empty($categories) || in_array(InsightCategory::ORDERS->value, $categories)) {
                $totalOrders = $ordersByPeriod->sum('total_orders');
                $totalSales = $ordersByPeriod->sum('total_grand_total');

                $totalOrdersByPeriod = $ordersByPeriod->pluck('total_orders', 'period')->sortByDesc(null);

                $mostOrderDay = 'N/A';
                $leastOrderDay = 'N/A';

                if ($totalOrdersByPeriod->isNotEmpty()) {
                    $mostOrderPeriod = $totalOrdersByPeriod->keys()->first();
                    $mostOrderCount = $totalOrdersByPeriod->get($mostOrderPeriod, 0);

                    $leastOrderPeriod = $totalOrdersByPeriod->keys()->last();
                    $leastOrderCount = $totalOrdersByPeriod->get($leastOrderPeriod, 0);

                    $mostOrderDay = "{$mostOrderPeriod} ({$mostOrderCount} " . ($mostOrderCount == 1 ? 'order' : 'orders') . ")";

                    $leastOrderPeriods = $totalOrdersByPeriod->filter(fn($count) => $count === $leastOrderCount)->keys();

                    if ($leastOrderPeriods->count() === 1 && $mostOrderCount !== $leastOrderCount) {
                        $leastOrderDay = "{$leastOrderPeriods->first()} ({$leastOrderCount} " . ($leastOrderCount == 1 ? 'order' : 'orders') . ")";
                    }
                }

                $totalSalesFormatted = MoneyService::convertToMoneyFormat($totalSales, $store->getRawOriginal('currency'))->amount_with_currency;

                $add(
                    'Order Insights',
                    'Store performance based on orders',
                    [
                        [$isUssd ? 'Orders' : 'Total orders', "{$totalOrders} ({$totalSalesFormatted})", 'total_orders', 'The total number of orders placed, along with the total sales revenue generated from those orders'],
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
                    ->groupBy('product_id', 'products.name')
                    ->orderByDesc('total_revenue')
                    ->orderByDesc('total_quantity')
                    ->get();

                $topSelling = 'N/A';
                $leastSelling = 'N/A';

                if ($productsBySales->isNotEmpty()) {
                    $topSellingProduct = $productsBySales->first();
                    $topSelling = "{$topSellingProduct->product_name}";

                    $leastSellingProduct = $productsBySales->last();
                    $matchingLeastSellingProducts = $productsBySales->filter(fn($product) =>
                        $product->total_revenue === $leastSellingProduct->total_revenue &&
                        $product->total_quantity === $leastSellingProduct->total_quantity
                    );

                    if ($matchingLeastSellingProducts->count() === 1 && $topSellingProduct->product_id !== $leastSellingProduct->product_id) {
                        $leastSelling = "{$leastSellingProduct->product_name}";
                    }
                }

                $totalQuantity = $productsBySales->sum('total_quantity');
                $totalProductRevenue = $productsBySales->sum('total_revenue');
                $totalCancelledQuantity = $productsBySales->sum('cancelled_quantity');
                $totalCancelledRevenue = $productsBySales->sum('cancelled_revenue');
                $totalDiscount = $productsBySales->sum('total_discount');

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
                        ['Products sold', "{$totalQuantity} ({$totalProductRevenueFormatted})", 'products_sold', 'The total quantity of products sold, along with the total revenue generated from their sales'],
                        ['Products cancelled', "{$totalCancelledQuantity} ({$totalCancelledRevenueFormatted})", 'products_cancelled', 'The total number of products cancelled on placed orders, along with the total revenue associated with those cancellations'],
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
                    ->groupBy('customers.id')
                    ->get();

                $totalCustomers = $customersData->count();
                $newCustomers = $customersData->filter(fn($record) => $record->total_orders == 1)->count();
                $returnCustomers = $customersData->filter(fn($record) => $record->total_orders > 1)->count();
                $retentionRate = $totalCustomers > 0 ? ($returnCustomers / $totalCustomers) * 100 : 0;

                $totalRevenue = $customersData->sum('total_spend');
                $revenuePerCustomer = $totalCustomers > 0 ? $totalRevenue / $totalCustomers : 0;
                $revenuePerCustomerFormatted = MoneyService::convertToMoneyFormat($revenuePerCustomer, $store->getRawOriginal('currency'))->amount_with_currency;

                [$previousDateRange1, $previousDateRange2] = match ($period) {
                    InsightPeriod::TODAY->value => [$dateRange1->copy()->subDay(), $dateRange2->copy()->subDay()],
                    InsightPeriod::YESTERDAY->value => [$dateRange1->copy()->subDays(2), $dateRange2->copy()->subDay(2)],
                    InsightPeriod::THIS_WEEK->value => [$dateRange1->copy()->subWeek(), $dateRange2->copy()->subWeek()],
                    InsightPeriod::THIS_MONTH->value => [$dateRange1->copy()->subMonth(), $dateRange2->copy()->subMonth()],
                    InsightPeriod::THIS_YEAR->value => [$dateRange1->copy()->subYear(), $dateRange2->copy()->subYear()],
                    default => [$dateRange1->copy()->subDay(), $dateRange2->copy()->subDay()]
                };

                $previousPeriodCustomers = DB::table('customers')
                    ->leftJoin('orders', 'customers.id', '=', 'orders.customer_id')
                    ->where('orders.store_id', $store->id)
                    ->whereBetween('orders.created_at', [$previousDateRange1, $previousDateRange2])
                    ->distinct('customers.id')
                    ->count('customers.id');

                $customerGrowthRate = $previousPeriodCustomers == 0 && $totalCustomers > 0
                    ? 100
                    : ($previousPeriodCustomers > 0 ? (($totalCustomers - $previousPeriodCustomers) / $previousPeriodCustomers) * 100 : 0);

                $add(
                    'Customer Insights',
                    'Store performance based on customers',
                    [
                        ['Total customers', $totalCustomers, 'total_customers', 'The total number of unique customers on this store'],
                        ['New customers', $newCustomers, 'new_customers', 'The number of customers who have only placed one order'],
                        ['Return customers', $returnCustomers, 'return_customers', 'The number of customers who have placed more than one order'],
                        ['Retention Rate', sprintf('%.1f%%', $retentionRate), 'retention_rate', 'The percentage of customers who made repeat purchases, indicating customer loyalty'],
                        ['Revenue per Customer', $revenuePerCustomerFormatted, 'revenue_per_customer', 'The average revenue generated from each customer, calculated as total sales divided by the total number of customers'],
                        ['Customer Growth Rate', sprintf('%.1f%% (%s)', $customerGrowthRate, $customerGrowthRate > 0 ? 'increased' : ($customerGrowthRate == 0 ? 'no change' : 'decreased')), 'customer_growth_rate', 'The rate at which the customer base has grown or decreased, expressed as a percentage']
                    ]
                );
            }

            if (empty($categories) || in_array(InsightCategory::OPERATIONS->value, $categories)) {
                // TODO: Implement operations insights (e.g., order fulfillment times, staff performance)
                $add(
                    'Operations Insights',
                    'Store performance based on operational metrics',
                    [
                        ['Operational Metrics', 'N/A', 'operational_metrics', 'Operational insights are not yet implemented']
                    ]
                );
            }
        } catch (Exception $e) {
            return ['insights' => []]; // Return empty insights on failure
        }

        return ['insights' => $insights];
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
     * Build store design cards.
     *
     * @param Store $store
     */
    public function buildStoreDesignCards(Store $store)
    {
        $categoryId = $store->categories()->withCount('products')->orderByDesc('products_count')->pluck('id')->first();
        $contactMobileNumber = $store->whatsapp_mobile_number?->formatE164() ?? $store->ussd_mobile_number?->formatE164() ?? $store->invoice_company_mobile_number?->formatE164() ?? '';

        if($store->whatsapp_mobile_number) {
            $bannerTitle = 'Reach Us On Whatsapp';
            $bannerLink = 'https://wa.me/'.ltrim($store->whatsapp_mobile_number?->formatE164(), '+').'?text='.urlencode('Hello, I\'m interested in some products i found at your '.$store->name.' shop');
        }else {
            $bannerTitle = '';
            $bannerLink = '';
        }

        $storefront = [
            [
                'type' => 'banner',
                'placement' => 'storefront',
                'metadata' => [
                    'link' => $bannerLink,
                    'title' => $bannerTitle,
                    'design' => [
                        'b_border' => '0',
                        'b_margin' => '8',
                        'bg_color' => '#01a045',
                        'l_border' => '0',
                        'l_margin' => '0',
                        'r_border' => '0',
                        'r_margin' => '0',
                        't_border' => '0',
                        't_margin' => '0',
                        'b_padding' => '16',
                        'l_padding' => '16',
                        'r_padding' => '16',
                        't_padding' => '16',
                        'text_color' => '#ffffff',
                        'title_color' => '#111827',
                        'border_color' => '#E5E7EB',
                        'bl_border_radius' => '0',
                        'br_border_radius' => '0',
                        'tl_border_radius' => '0',
                        'tr_border_radius' => '0'
                    ]
                ],
            ],
            [
                'type' => 'logo',
                'placement' => 'storefront',
                'metadata' => [
                    'alignment' => 'center',
                    'design' => [
                        'b_border' => '0',
                        'b_margin' => '8',
                        'bg_color' => null,
                        'l_border' => '0',
                        'l_margin' => '0',
                        'r_border' => '0',
                        'r_margin' => '0',
                        't_border' => '0',
                        't_margin' => '0',
                        'b_padding' => '16',
                        'l_padding' => '16',
                        'r_padding' => '16',
                        't_padding' => '16',
                        'title_color' => '#111827',
                        'border_color' => '#E5E7EB',
                        'bl_border_radius' => '0',
                        'br_border_radius' => '0',
                        'tl_border_radius' => '0',
                        'tr_border_radius' => '0'
                    ]
                ],
            ],
            [
                'type' => 'text',
                'placement' => 'storefront',
                'metadata' => [
                    'body' => ('Welcome to '.$store->name.' shop') . (!empty($store->description) ? ' .'.$store->description : ''),
                    'design' => [
                        'b_border' => '2',
                        'b_margin' => '0',
                        'bg_color' => '#e3f2e5ff',
                        'l_border' => '0',
                        'l_margin' => '0',
                        'r_border' => '0',
                        'r_margin' => '0',
                        't_border' => '2',
                        't_margin' => '0',
                        'b_padding' => '15',
                        'l_padding' => '15',
                        'r_padding' => '15',
                        't_padding' => '15',
                        'text_color' => '#111827',
                        'title_color' => '#111827',
                        'border_color' => '#c8e6caff',
                        'bl_border_radius' => '0',
                        'br_border_radius' => '0',
                        'tl_border_radius' => '0',
                        'tr_border_radius' => '0'
                    ]
                ],
            ],
            [
                'type' => 'products',
                'placement' => 'storefront',
                'metadata' => [
                    'feature' => '4',
                    'layout' => 'grid',
                    'category_id' => $categoryId,
                    'design' => [
                        'b_border' => '0',
                        'b_margin' => '8',
                        'bg_color' => '#ffffff',
                        'l_border' => '0',
                        'l_margin' => '4',
                        'r_border' => '0',
                        'r_margin' => '4',
                        't_border' => '0',
                        't_margin' => '0',
                        'b_padding' => '16',
                        'l_padding' => '16',
                        'r_padding' => '16',
                        't_padding' => '16',
                        'title_color' => '#111827',
                        'border_color' => '#E5E7EB',
                        'bl_border_radius' => '8',
                        'br_border_radius' => '8',
                        'tl_border_radius' => '8',
                        'tr_border_radius' => '8',
                        'description_color' => '#4B5563',
                        'product_name_color' => '#111827',
                        'product_price_color' => '#111827',
                        'product_cancelled_price_color' => '#9CA3AF'
                    ],
                ],
            ],
            [
                'type' => 'divider',
                'placement' => 'storefront',
                'metadata' => [
                    'thickness' => '1',
                    'divider' => 'solid',
                    'design' => [
                        'b_border' => '0',
                        'b_margin' => '8',
                        'bg_color' => null,
                        'l_border' => '0',
                        'l_margin' => '0',
                        'r_border' => '0',
                        'r_margin' => '0',
                        't_border' => '0',
                        't_margin' => '8',
                        'b_padding' => '16',
                        'l_padding' => '16',
                        'r_padding' => '16',
                        't_padding' => '16',
                        'title_color' => '#111827',
                        'border_color' => '#E5E7EB',
                        'divider_color' => '#e9e9ecff',
                        'bl_border_radius' => '0',
                        'br_border_radius' => '0',
                        'tl_border_radius' => '0',
                        'tr_border_radius' => '0'
                    ]
                ],
            ],
            [
                'type' => 'contact',
                'placement' => 'storefront',
                'metadata' => [
                    'title' => 'Contact Us',
                    'mobile_number' => $contactMobileNumber,
                    'design' => [
                        'b_border' => '1',
                        'b_margin' => '8',
                        'bg_color' => '#ffffff',
                        'l_border' => '1',
                        'l_margin' => '4',
                        'r_border' => '1',
                        'r_margin' => '4',
                        't_border' => '1',
                        't_margin' => '0',
                        'b_padding' => '16',
                        'l_padding' => '16',
                        'r_padding' => '16',
                        't_padding' => '16',
                        'icon_color' => '#6B7280',
                        'title_color' => '#111827',
                        'border_color' => '#E5E7EB',
                        'bl_border_radius' => '16',
                        'br_border_radius' => '16',
                        'tl_border_radius' => '16',
                        'tr_border_radius' => '16'
                    ]
                ],
            ]
        ];

        $checkout = [
            [
                'type' => 'logo',
                'placement' => 'checkout',
                'metadata' => [
                    'alignment' => 'center',
                    'design' => [
                        'b_border' => '0',
                        'b_margin' => '8',
                        'bg_color' => null,
                        'l_border' => '0',
                        'l_margin' => '0',
                        'r_border' => '0',
                        'r_margin' => '0',
                        't_border' => '0',
                        't_margin' => '0',
                        'b_padding' => '16',
                        'l_padding' => '16',
                        'r_padding' => '16',
                        't_padding' => '16',
                        'border_color' => '#000000',
                        'bl_border_radius' => '0',
                        'br_border_radius' => '0',
                        'tl_border_radius' => '0',
                        'tr_border_radius' => '0'
                    ]
                ],
            ],
            [
                'type' => 'customer',
                'placement' => 'checkout',
                'metadata' => [
                    'title' => 'Customer',
                    'show_email' => false,
                    'description' => null,
                    'email_required' => false,
                    'show_last_name' => false,
                    'show_first_name' => true,
                    'last_name_required' => false,
                    'show_mobile_number' => true,
                    'first_name_required' => true,
                    'mobile_number_required' => true,
                    'design' => [
                        'b_border' => '0',
                        'b_margin' => '8',
                        'bg_color' => '#ffffff',
                        'l_border' => '0',
                        'l_margin' => '4',
                        'r_border' => '0',
                        'r_margin' => '4',
                        't_border' => '0',
                        't_margin' => '0',
                        'b_padding' => '16',
                        'l_padding' => '16',
                        'r_padding' => '16',
                        't_padding' => '16',
                        'title_color' => '#111827',
                        'border_color' => '#E5E7EB',
                        'bl_border_radius' => '16',
                        'br_border_radius' => '16',
                        'tl_border_radius' => '16',
                        'tr_border_radius' => '16',
                        'description_color' => '#111827',
                        'optional_text_color' => '#9CA3AF'
                    ]
                ],
            ],
            [
                'type' => 'items',
                'placement' => 'checkout',
                'metadata' => [
                    'title' => 'Items',
                    'show_items' => true,
                    'description' => null,
                    'design' => [
                        'b_border' => '0',
                        'b_margin' => '8',
                        'bg_color' => '#ffffff',
                        'l_border' => '0',
                        'l_margin' => '4',
                        'r_border' => '0',
                        'r_margin' => '4',
                        't_border' => '0',
                        't_margin' => '0',
                        'b_padding' => '16',
                        'l_padding' => '16',
                        'r_padding' => '16',
                        't_padding' => '16',
                        'title_color' => '#111827',
                        'border_color' => '#E5E7EB',
                        'bl_border_radius' => '16',
                        'br_border_radius' => '16',
                        'tl_border_radius' => '16',
                        'tr_border_radius' => '16',
                        'description_color' => '#111827'
                    ]
                ],
            ],
            [
                'type' => 'delivery',
                'placement' => 'checkout',
                'metadata' => [
                    'title' => 'Delivery Methods',
                    'description' => null,
                    'address_title' => 'Address',
                    'schedule_title' => 'Schedule',
                    'show_delivery_methods' => true,
                    'design' => [
                        'b_border' => '0',
                        'b_margin' => '8',
                        'bg_color' => '#ffffff',
                        'l_border' => '0',
                        'l_margin' => '4',
                        'r_border' => '0',
                        'r_margin' => '4',
                        't_border' => '0',
                        't_margin' => '0',
                        'b_padding' => '16',
                        'l_padding' => '16',
                        'r_padding' => '16',
                        't_padding' => '16',
                        'title_color' => '#111827',
                        'border_color' => '#E5E7EB',
                        'bl_border_radius' => '16',
                        'br_border_radius' => '16',
                        'tl_border_radius' => '16',
                        'tr_border_radius' => '16',
                        'description_color' => '#111827'
                    ]
                ],
            ],
            [
                'type' => 'promo code',
                'placement' => 'checkout',
                'metadata' => [
                    'title' => 'Promo code',
                    'description' => null,
                    'show_promo_code' => true,
                    'design' => [
                        'b_border' => '0',
                        'b_margin' => '8',
                        'bg_color' => '#ffffff',
                        'l_border' => '0',
                        'l_margin' => '4',
                        'r_border' => '0',
                        'r_margin' => '4',
                        't_border' => '0',
                        't_margin' => '0',
                        'b_padding' => '16',
                        'l_padding' => '16',
                        'r_padding' => '16',
                        't_padding' => '16',
                        'title_color' => '#111827',
                        'border_color' => '#E5E7EB',
                        'bl_border_radius' => '16',
                        'br_border_radius' => '16',
                        'tl_border_radius' => '16',
                        'tr_border_radius' => '16',
                        'description_color' => '#111827'
                    ]
                ],
            ],
            [
                'type' => 'tips',
                'placement' => 'checkout',
                'metadata' => [
                    'title' => 'Tip',
                    'show_tips' => true,
                    'description' => null,
                    'tips' => ['10', '20'],
                    'show_specify_tip' => true,
                    'design' => [
                        'b_border' => '0',
                        'b_margin' => '8',
                        'bg_color' => '#ffffff',
                        'l_border' => '0',
                        'l_margin' => '4',
                        'r_border' => '0',
                        'r_margin' => '4',
                        't_border' => '0',
                        't_margin' => '0',
                        'b_padding' => '16',
                        'l_padding' => '16',
                        'r_padding' => '16',
                        't_padding' => '16',
                        'title_color' => '#111827',
                        'border_color' => '#E5E7EB',
                        'pill_bg_color' => '#DBEAFE',
                        'pill_text_color' => '#1E40AF',
                        'bl_border_radius' => '16',
                        'br_border_radius' => '16',
                        'tl_border_radius' => '16',
                        'tr_border_radius' => '16',
                        'description_color' => '#111827'
                    ]
                ],
            ],
            [
                'type' => 'order summary',
                'placement' => 'checkout',
                'metadata' => [
                    'title' => 'Order Summary',
                    'description' => null,
                    'combine_fees' => false,
                    'checkout_fees' => [],
                    'combine_discounts' => false,
                    'design' => [
                        'b_border' => '0',
                        'b_margin' => '8',
                        'bg_color' => '#ffffff',
                        'l_border' => '0',
                        'l_margin' => '4',
                        'r_border' => '0',
                        'r_margin' => '4',
                        't_border' => '0',
                        't_margin' => '0',
                        'b_padding' => '16',
                        'l_padding' => '16',
                        'r_padding' => '16',
                        't_padding' => '16',
                        'title_color' => '#111827',
                        'border_color' => '#E5E7EB',
                        'bl_border_radius' => '16',
                        'br_border_radius' => '16',
                        'tl_border_radius' => '16',
                        'tr_border_radius' => '16',
                        'description_color' => '#111827'
                    ]
                ],
            ]
        ];

        $payment = [
            [
                'type' => 'logo',
                'placement' => 'payment',
                'metadata' => [
                    'alignment' => 'center',
                    'design' => [
                        'b_border' => '0',
                        'b_margin' => '0',
                        'bg_color' => null,
                        'l_border' => '0',
                        'l_margin' => '0',
                        'r_border' => '0',
                        'r_margin' => '0',
                        't_border' => '0',
                        't_margin' => '20',
                        'b_padding' => '16',
                        'l_padding' => '16',
                        'r_padding' => '16',
                        't_padding' => '16',
                        'border_color' => '#000000',
                        'bl_border_radius' => '0',
                        'br_border_radius' => '0',
                        'tl_border_radius' => '0',
                        'tr_border_radius' => '0'
                    ]
                ]
            ],
            [
                'type' => 'payment methods',
                'placement' => 'payment',
                'metadata' => [
                    'title' => 'Complete Your Payment',
                    'subtitle' => 'Amount to pay',
                    'design' => [
                        'b_border' => '0',
                        'b_margin' => '8',
                        'bg_color' => null,
                        'l_border' => '0',
                        'l_margin' => '4',
                        'r_border' => '0',
                        'r_margin' => '4',
                        't_border' => '0',
                        't_margin' => '0',
                        'b_padding' => '16',
                        'l_padding' => '16',
                        'r_padding' => '16',
                        't_padding' => '16',
                        'title_color' => '#111827',
                        'amount_color' => '#111827',
                        'border_color' => '#E5E7EB',
                        'subtitle_color' => '#111827',
                        'bl_border_radius' => '16',
                        'br_border_radius' => '16',
                        'tl_border_radius' => '16',
                        'tr_border_radius' => '16'
                    ]
                ],
            ]
        ];

        $menu = [
            [
                'type' => 'logo',
                'placement' => 'menu',
                'metadata' => [
                    'design' => [
                        'b_border' => '0',
                        'b_margin' => '8',
                        'bg_color' => null,
                        'l_border' => '0',
                        'l_margin' => '0',
                        'r_border' => '0',
                        'r_margin' => '0',
                        't_border' => '0',
                        't_margin' => '0',
                        'b_padding' => '16',
                        'l_padding' => '16',
                        'r_padding' => '16',
                        't_padding' => '16',
                        'title_color' => '#111827',
                        'border_color' => '#E5E7EB',
                        'bl_border_radius' => '0',
                        'br_border_radius' => '0',
                        'tl_border_radius' => '0',
                        'tr_border_radius' => '0'
                    ],
                    'alignment' => 'center'
                ],
            ],
            [
                'type' => 'link',
                'placement' => 'menu',
                'metadata' => [
                    'link' => 'https://www.example.com',
                    'title' => 'Home',
                    'design' => [
                        'b_border' => '1',
                        'b_margin' => '16',
                        'bg_color' => '#ffffff',
                        'l_border' => '1',
                        'l_margin' => '16',
                        'r_border' => '1',
                        'r_margin' => '16',
                        't_border' => '1',
                        't_margin' => '16',
                        'b_padding' => '8',
                        'l_padding' => '8',
                        'r_padding' => '8',
                        't_padding' => '8',
                        'icon_color' => '#6B7280',
                        'title_color' => '#111827',
                        'border_color' => '#E5E7EB',
                        'bl_border_radius' => '4',
                        'br_border_radius' => '4',
                        'tl_border_radius' => '4',
                        'tr_border_radius' => '4'
                    ]
                ],
            ],
            [
                'type' => 'categories',
                'placement' => 'menu',
                'metadata' => [
                    'title' => 'Categories',
                    'design' => [
                        'b_border' => '0',
                        'b_margin' => '0',
                        'bg_color' => null,
                        'l_border' => '0',
                        'l_margin' => '0',
                        'r_border' => '0',
                        'r_margin' => '0',
                        't_border' => '0',
                        't_margin' => '0',
                        'b_padding' => '0',
                        'l_padding' => '0',
                        'r_padding' => '0',
                        't_padding' => '0',
                        'title_color' => '#111827',
                        'border_color' => '#E5E7EB',
                        'bl_border_radius' => '0',
                        'br_border_radius' => '0',
                        'tl_border_radius' => '0',
                        'tr_border_radius' => '0'
                    ]
                ],
            ],
            [
                'type' => 'divider',
                'placement' => 'menu',
                'metadata' => [
                    'thickness' => '1',
                    'divider' => 'solid',
                    'design' => [
                        'b_border' => '0',
                        'b_margin' => '16',
                        'bg_color' => null,
                        'l_border' => '0',
                        'l_margin' => '16',
                        'r_border' => '0',
                        'r_margin' => '16',
                        't_border' => '0',
                        't_margin' => '16',
                        'b_padding' => '0',
                        'l_padding' => '0',
                        'r_padding' => '0',
                        't_padding' => '0',
                        'title_color' => '#111827',
                        'border_color' => '#E5E7EB',
                        'divider_color' => '#f0f0f0ff',
                        'bl_border_radius' => '0',
                        'br_border_radius' => '0',
                        'tl_border_radius' => '0',
                        'tr_border_radius' => '0'
                    ]
                ],
            ],
            [
                'type' => 'socials',
                'placement' => 'menu',
                'metadata' => [
                    'title' => null,
                    'platforms' => [],
                    'design' => [
                        'b_border' => '0',
                        'b_margin' => '0',
                        'bg_color' => '#ffffff',
                        'l_border' => '0',
                        'l_margin' => '0',
                        'r_border' => '0',
                        'r_margin' => '0',
                        't_border' => '0',
                        't_margin' => '0',
                        'b_padding' => '0',
                        'l_padding' => '0',
                        'r_padding' => '0',
                        't_padding' => '0',
                        'icon_color' => '#6B7280',
                        'title_color' => '#111827',
                        'border_color' => '#E5E7EB',
                        'bl_border_radius' => '16',
                        'br_border_radius' => '16',
                        'tl_border_radius' => '16',
                        'tr_border_radius' => '16'
                    ]
                ],
            ],
            [
                'type' => 'install_app',
                'placement' => 'menu',
                'metadata' => [
                    'button_text' => 'Install Our App',
                    'design' => [
                        'b_border' => '0',
                        'b_margin' => '8',
                        'bg_color' => '#ffffff',
                        'l_border' => '0',
                        'l_margin' => '0',
                        'r_border' => '0',
                        'r_margin' => '0',
                        't_border' => '0',
                        't_margin' => '24',
                        'b_padding' => '0',
                        'l_padding' => '0',
                        'r_padding' => '0',
                        't_padding' => '0',
                        'title_color' => '#111827',
                        'border_color' => '#E5E7EB',
                        'button_color' => '#1E40AF',
                        'bl_border_radius' => '0',
                        'br_border_radius' => '0',
                        'tl_border_radius' => '0',
                        'tr_border_radius' => '0',
                        'button_text_color' => '#ffffff'
                    ]
                ],
            ]
        ];

        $designCards = array_merge($storefront, $checkout, $payment, $menu);

        foreach ($designCards as $key => $designCard) {
            $designCards[$key]['visible'] = 1;
            $designCards[$key]['id'] = Str::uuid();
            $designCards[$key]['created_at'] = now();
            $designCards[$key]['updated_at'] = now();
            $designCards[$key]['position'] = $key + 1;
            $designCards[$key]['store_id'] = $store->id;
            $designCards[$key]['metadata'] = json_encode($designCard['metadata']);
        }

        DB::table('design_cards')->where('store_id', $store->id)->delete();
        DB::table('design_cards')->insert($designCards);
    }
}
