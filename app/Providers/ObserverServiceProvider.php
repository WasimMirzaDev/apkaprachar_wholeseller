<?php

namespace App\Providers;

use App\Models\Product;
use App\Models\Order;
use App\Models\Review;
use App\Models\ProductStock;
use App\Models\ProductTag;
use App\Models\ProductCompare;
use App\Observers\BusinessSettingsObserver;
use App\Observers\CurrencyObserver;
use App\Observers\OrderObserver;
use App\Observers\ProductObserver;
use App\Observers\ProductStockObserver;
use App\Observers\ProductTagObserver;
use App\Observers\ProductCompareObserver;
use App\Observers\ReviewObserver;
use App\Observers\TranslationObserver;
use Illuminate\Support\ServiceProvider;

class ObserverServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        Product::observe([
            ProductObserver::class,
//             OrderObserver::class,
//             BusinessSettingsObserver::class,
//             CurrencyObserver::class,
//             TranslationObserver::class
        ]);
        
        Review::observe([
            ReviewObserver::class,
        ]);
        
        ProductCompare::observe([
            ProductCompareObserver::class,
        ]);
        
        ProductTag::observe([
            ProductTagObserver::class,
        ]);
        
        ProductStock::observe([
            ProductStockObserver::class,
        ]);
        
        Order::observe([
            OrderObserver::class,
        ]);
    }
}
