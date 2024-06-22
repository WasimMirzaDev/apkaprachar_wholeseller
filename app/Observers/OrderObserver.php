<?php

namespace App\Observers;

use App\Models\Order;
use App\Traits\PushNotificationTrait;
use App\Services\ExportOrderService;

class OrderObserver
{
    use PushNotificationTrait;
    
    protected $exportOrderService;

    public function __construct(ExportOrderService $exportOrderService)
    {
        $this->exportOrderService = $exportOrderService;
    }
    
    /**
     * Handle the Order "created" event.
     */
    public function created(Order $order): void
    {
//        $order->flushCache();
    }

    /**
     * Handle the Order "updated" event.
     */
    public function updated(Order $order): void
    {
//        $order->flushCache();
        $this->exportOrderService->sync($order);
    }

    /**
     * Handle the Order "deleted" event.
     */
    public function deleted(Order $order): void
    {
        //
    }

    /**
     * Handle the Order "restored" event.
     */
    public function restored(Order $order): void
    {
        //
    }

    /**
     * Handle the Order "force deleted" event.
     */
    public function forceDeleted(Order $order): void
    {
        //
    }
}
