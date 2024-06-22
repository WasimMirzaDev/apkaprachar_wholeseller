<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\Request;
use App\Utils\Helpers;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Session;

class ExportDataController extends Controller
{
    public function exportData(Request $request)
    {
        $user        = auth()->guard('customer')->user();
        $subDomain  =  $user->sub_domain ?? '';
        if($subDomain){
            $createToken = $user->createToken('exchange-data-token');
            $apiToken    = $createToken->accessToken;
            // Determine subdomain URL dynamically based on the reseller's information
            $subdomainUrl = $subDomain.'/api/v4/product/import';

            //getProductDataByMainApplication
            $productData = Product::with(['stocks','brand','shop','category','subCategory','subSubCategory','compareList','reviews.customer'])->find($request->product_id);
        
            $response = Http::withHeaders([
                        'Authorization' => 'Bearer ' . $apiToken, 
                        'Accept' => 'application/json',
                    ])->post($subdomainUrl, [
                        'product_data' => $productData, // Replace with actual product data
                    ]);

            $responseData = $response->json();    
        }else{
            return response()->json(['status'=>'error','msg'=>'Reseller sub domain not added yet!']);
        }
        
        
        // Check if the request was successful
        if ($response->status() === 200) {
            $productData->is_exported = 1;
            $productData->save();
            return response()->json(['status'=>'success','msg' => translate($responseData['message'])]);
        } else {
            return response()->json(['status'=>'error','msg' => translate($responseData['message'])]);
        }
    }
}