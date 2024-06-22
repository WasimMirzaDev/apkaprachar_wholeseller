<?php

namespace App\Observers;

use App\Models\ProductTag;
use Illuminate\Support\Facades\Log;
use App\Services\ExportProductService;

class ProductTagObserver
{
    protected $exportProductService;

    public function __construct(ExportProductService $exportProductService)
    {
        $this->exportProductService = $exportProductService;
    }
    
    /**
     * Handle the Product "created" event.
     */
    public function created(ProductTag $productTag): void
    {
//        $product->flushQueryCache();
        $this->exportProductService->sync($productTag);
    }

    /**
     * Handle the Product "updated" event.
     */
    public function updated(ProductTag $productTag): void
    {
//        $product->flushQueryCache();

        //ADDED BY ABDUL MANNAN TO SYNC PRODUCT INVENTORY IN ALL RESELLER DATABASE
        $this->exportProductService->sync($productTag);
    }

    /**
     * Handle the Product "deleted" event.
     */
    public function deleted(ProductTag $productTag): void
    {
//        $product->flushCache();
    }

    /**
     * Handle the Product "restored" event.
     */
    public function restored(ProductTag $productTag): void
    {
//        $product->flushCache();
    }

    /**
     * Handle the Product "force deleted" event.
     */
    public function forceDeleted(ProductTag $productTag): void
    {
//        $product->flushCache();
    }
}
