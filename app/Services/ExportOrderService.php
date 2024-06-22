<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use App\Models\Order;
use Carbon\Carbon;

class ExportOrderService extends ExportProductService
{
    public function sync($model)
    {
        $order = Order::find($model->id);
        if($order->imported_order_id != null){
            try{
                $updateStatusEndPoint = $order->reseller.'/api/v4/order/update-status';
                
                $response = Http::post($updateStatusEndPoint, [
                        'order' => $order,
                    ]);
                
                $responseData = $response->json();
                // Check if the request was successful
                if ($response->status() === 200) {
                    $this->generateLog('success', 'Order status update', 'Successfully updated', $order->id);
                } else {
                    $this->generateLog('error', 'Order status update', 'There was an error updating order status');
                }
            }catch(Exception $e){
                $this->generateLog('error', 'Order status update', $e->getMessage());
            }
        }
    }
}