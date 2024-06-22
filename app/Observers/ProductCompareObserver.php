<?php

namespace App\Observers;

use App\Models\ProductCompare;
use Illuminate\Support\Facades\Log;
use App\Services\ExportProductService;

class ProductCompareObserver
{
    protected $exportProductService;

    public function __construct(ExportProductService $exportProductService)
    {
        $this->exportProductService = $exportProductService;
    }
    
    /**
     * Handle the Product "created" event.
     */
    public function created(ProductCompare $productCompare): void
    {
//        $product->flushQueryCache();
        $this->exportProductService->sync($productCompare);
    }

    /**
     * Handle the Product "updated" event.
     */
    public function updated(ProductCompare $productCompare): void
    {
//        $product->flushQueryCache();

        //ADDED BY ABDUL MANNAN TO SYNC PRODUCT INVENTORY IN ALL RESELLER DATABASE
        $this->exportProductService->sync($productCompare);
    }

    /**
     * Handle the Product "deleted" event.
     */
    public function deleted(ProductCompare $productCompare): void
    {
//        $product->flushCache();
    }

    /**
     * Handle the Product "restored" event.
     */
    public function restored(ProductCompare $productCompare): void
    {
//        $product->flushCache();
    }

    /**
     * Handle the Product "force deleted" event.
     */
    public function forceDeleted(ProductCompare $productCompare): void
    {
//        $product->flushCache();
    }
}
