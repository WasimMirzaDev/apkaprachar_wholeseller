<?php

namespace App\Http\Controllers\RestAPI\v4;

use App\Http\Controllers\Controller;
use App\Utils\Helpers;
use App\Models\Order;
use App\Models\ShippingAddress;
use App\Models\User;
use App\Models\OrderDetail;
use App\Services\ExportProductService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ImportDataController extends Controller
{
    public function __construct(protected ExportProductService $exportProductService)
    {
        
    }
    
    public function importData(Request $request)
    {
        try {
            // Extract the Order data from the request
            $orderData = $request->input('orderData');
            $orderUrl = $request->input('orderUrl');
            // Begin a database transaction
            DB::beginTransaction();

            // Extract and save shipping address
            $shippingAddress = $orderData['shipping_address_data'] ?? [];
            if($shippingAddress){
                $shipping = new ShippingAddress;
                $shipping->fill($shippingAddress);
                $shipping->created_at = now();
                $shipping->updated_at = now();
                $shipping->save();   
            }
            
            $userEmail = User::where('email',$orderData['customer']['email'])->first();
            
            if(!isset($userEmail)){
                $customer = new User;
                $customer->f_name       =   $orderData['customer']['f_name'];
                $customer->l_name       =   $orderData['customer']['l_name'];
                $customer->email        =   $orderData['customer']['email'];
                $customer->phone        =   $orderData['customer']['phone'];
                $customer->is_active    =   $orderData['customer']['is_active'];
                $customer->app_language =   $orderData['customer']['app_language'];
                $customer->is_imported  =   1;
                $customer->created_at   =   now();
                $customer->updated_at   =   now();
                $customer->save();
                $customerId = $customer->id;
            }else{
                $customerId = $userEmail->id;
            }
            // Add order
            $msg = $this->createOrder($orderData, $shipping->id, $customerId, $orderUrl);

            // Commit the transaction
            DB::commit();

            // Return success response
            $this->exportProductService->generateLog('success', 'Order', $msg, $orderData['id']);
            return response()->json(['message' => $msg], 200);
        } catch (Exception $e) {
            // Catch any other exceptions and return an error response
            DB::rollBack();
            $this->exportProductService->generateLog('error', 'Order', $e->getMessage());
            return response()->json(['message' => 'An error occurred. Please try again later.'], 500);
        }
    }

    public function createOrder($orderData, $shippingId, $customerId, $orderUrl)
    {
        try {
            $orderCount = Order::count();
            $order_id = 100000 + $orderCount + 1;
            if (Order::find($order_id)) {
                $order_id = Order::orderBy('id', 'DESC')->first()->id + 1;
            }

            $order = new Order;
            $order->fill($orderData);
            $order->verification_code = rand(100000, 999999);
            $order->id = $order_id;
            $order->created_at = now();
            $order->updated_at = now();
            $order->shipping_address = $shippingId;
            $order->shipping_address_data = ShippingAddress::find($shippingId);
            $order->billing_address = $shippingId;
            $order->billing_address_data = ShippingAddress::find($shippingId);
            $order->customer_id = $customerId;
            $order->imported_order_id = $orderData['id'];
            $order->reseller = $orderUrl;
            $order->save();

            $this->createOrderDetails($orderData['details'], $order->id);

            return "Order sent to seller successfully.";
        } catch (Exception $e) {
            $this->exportProductService->generateLog('error', 'Order', $e->getMessage());
            throw new Exception("Failed to create order: " . $e->getMessage());
        }
    }

    public function createOrderDetails($details, $orderId)
    {
        try {
            foreach ($details as $detail) {
                $orderDetail = new OrderDetail;
                $orderDetail->fill($detail);
                $orderDetail->order_id = $orderId;
                $orderDetail->product_id = $detail['product']['main_product_id'];
                $orderDetail->save();
            }
        } catch (Exception $e) {
            throw new Exception("Failed to create order details: " . $e->getMessage());
        }
    }

}