<?php

namespace App\Observers;

use App\Models\Review;
use Illuminate\Support\Facades\Log;
use App\Services\ExportProductService;

class ReviewObserver
{
    protected $exportProductService;

    public function __construct(ExportProductService $exportProductService)
    {
        $this->exportProductService = $exportProductService;
    }
    
    /**
     * Handle the Product "created" event.
     */
    public function created(Review $review): void
    {
//        $product->flushQueryCache();
        $this->exportProductService->sync($review);
    }

    /**
     * Handle the Product "updated" event.
     */
    public function updated(Review $review): void
    {
//        $product->flushQueryCache();

        //ADDED BY ABDUL MANNAN TO SYNC PRODUCT INVENTORY IN ALL RESELLER DATABASE
        $this->exportProductService->sync($review);
    }

    /**
     * Handle the Product "deleted" event.
     */
    public function deleted(Review $review): void
    {
//        $product->flushCache();
    }

    /**
     * Handle the Product "restored" event.
     */
    public function restored(Review $review): void
    {
//        $product->flushCache();
    }

    /**
     * Handle the Product "force deleted" event.
     */
    public function forceDeleted(Review $review): void
    {
//        $product->flushCache();
    }
}
