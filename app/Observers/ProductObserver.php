<?php

namespace App\Observers;

use App\Models\Product;

class ProductObserver
{
    public function saving(Product $product): Product
    {
        $product->on_sale = $product->determineIfOnSale();
        $product->unit_loss = $product->calculateUnitLoss();
        $product->has_price = $product->determineIfHasPrice();
        $product->has_stock = $product->determineIfHasStock();
        $product->unit_price = $product->calculateUnitPrice();
        $product->unit_profit = $product->calculateUnitProfit();
        $product->unit_sale_discount = $product->calculateUnitSaleDiscount();
        $product->unit_loss_percentage = $product->calculateUnitLossPercentage();
        $product->unit_profit_percentage = $product->calculateUnitProfitPercentage();
        $product->unit_sale_discount_percentage = $product->calculateUnitSaleDiscountPercentage();

        return $product;
    }

    public function creating(Product $product): void
    {
        //
    }

    public function created(Product $product): void
    {
        //
    }

    public function updated(Product $product): void
    {
        //
    }

    public function deleting(Product $product): void
    {
        //
    }

    public function deleted(Product $product): void
    {
        //
    }

    public function restored(Product $product): void
    {
        //
    }

    public function forceDeleted(Product $product): void
    {
        //
    }
}
