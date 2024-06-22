<?php

namespace App\Observers;

use App\Models\Product;
use Illuminate\Support\Facades\Log;
use App\Services\ExportProductService;

class ProductObserver
{
    protected $exportProductService;

    public function __construct(ExportProductService $exportProductService)
    {
        $this->exportProductService = $exportProductService;
    }
    
    /**
     * Handle the Product "created" event.
     */
    public function created(Product $product): void
    {
//        $product->flushQueryCache();
    }

    /**
     * Handle the Product "updated" event.
     */
    public function updated(Product $product): void
    {
//        $product->flushQueryCache();

        //ADDED BY ABDUL MANNAN TO SYNC PRODUCT INVENTORY IN ALL RESELLER DATABASE
        $this->exportProductService->sync($product);
    }

    /**
     * Handle the Product "deleted" event.
     */
    public function deleted(Product $product): void
    {
//        $product->flushCache();
    }

    /**
     * Handle the Product "restored" event.
     */
    public function restored(Product $product): void
    {
//        $product->flushCache();
    }

    /**
     * Handle the Product "force deleted" event.
     */
    public function forceDeleted(Product $product): void
    {
//        $product->flushCache();
    }
}
