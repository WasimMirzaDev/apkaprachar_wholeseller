<?php

namespace App\Observers;

use App\Models\ProductStock;
use Illuminate\Support\Facades\Log;
use App\Services\ExportProductService;

class ProductStockObserver
{
    protected $exportProductService;

    public function __construct(ExportProductService $exportProductService)
    {
        $this->exportProductService = $exportProductService;
    }
    
    /**
     * Handle the Product "created" event.
     */
    public function created(ProductStock $productStock): void
    {
//        $product->flushQueryCache();
        $this->exportProductService->sync($productStock);
    }

    /**
     * Handle the Product "updated" event.
     */
    public function updated(ProductStock $productStock): void
    {
//        $product->flushQueryCache();

        //ADDED BY ABDUL MANNAN TO SYNC PRODUCT INVENTORY IN ALL RESELLER DATABASE
        $this->exportProductService->sync($productStock);
    }

    /**
     * Handle the Product "deleted" event.
     */
    public function deleted(ProductStock $productStock): void
    {
//        $product->flushCache();
    }

    /**
     * Handle the Product "restored" event.
     */
    public function restored(ProductStock $productStock): void
    {
//        $product->flushCache();
    }

    /**
     * Handle the Product "force deleted" event.
     */
    public function forceDeleted(ProductStock $productStock): void
    {
//        $product->flushCache();
    }
}