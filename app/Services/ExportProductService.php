<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use App\Models\ProductCompare;
use App\Models\ProductStock;
use App\Models\ProductTag;
use App\Models\User;
use App\Models\Product;
use App\Models\Review;
use App\Models\ExportLog;
use Carbon\Carbon;

class ExportProductService
{
    protected string $exportLogClass;

    public function __construct()
    {
        $this->exportLogClass = ExportLog::class;
    }
    
    public function sync($model)
    {
        $id = $this->isExportable($model);
        if ($id) {
            try {
                $importedProduct = $this->getImportedProductData($id);
                if ($importedProduct['is_exported'] == 1) {
                    $this->updateProductInResellerDatabases($importedProduct);
                }
            } catch (Exception $e) {
                $this->generateLog('error', 'Product', $e->getMessage());
            }
        }
    }

    private function isExportable($model)
    {
        if ($model instanceof Product) {
            return $model->id;
        } elseif ($model instanceof ProductCompare || $model instanceof ProductStock || $model instanceof Review || $model instanceof ProductTag) {
            return $model->product_id;
        }
        return false;
    }

    private function getImportedProductData($id)
    {
        $importedProduct = Product::with([
            'stocks', 'brand', 'shop', 'category',
            'subCategory', 'subSubCategory', 'compareList', 'reviews.customer'
        ])->find($id)->toArray();
        unset($importedProduct['translations']);
        unset($importedProduct['brand']['translations']);
        unset($importedProduct['category']['translations']);
        unset($importedProduct['reviews']['translations']);

        return $importedProduct;
    }

    private function updateProductInResellerDatabases($importedProduct)
    {
        $resellers = User::where('is_active', 1)
                            ->where('is_reseller', 1)
                            ->whereNotNull('sub_domain')
                            ->distinct()
                            ->pluck('sub_domain');
        foreach ($resellers as $subDomain) {
            try {
                $resellerEndPoint = $subDomain.'/api/v4/product/update-stock';
                
                $response = Http::post($resellerEndPoint, [
                                'product' => $importedProduct,
                            ]);
                
                $responseData = $response->json();
                // Check if the request was successful
                if ($response->status() === 200) {
                    $this->generateLog('success', 'Product stock update',$subDomain.' || '.$responseData['message'], $importedProduct['id']);
                } else {
                    $this->generateLog('error', 'Product stock update', $subDomain.' || '. ($responseData['message'] ?? 'not found'));
                }
            } catch (Exception $e) {
                $this->generateLog('error', 'Product', $subDomain.' || '.$e->getMessage());
                continue;
            }
        }
    }

    public function generateLog($status, $type, $message, $id = null)
    {
        $log = new $this->exportLogClass();
        $log->status = $status;
        $log->run_date = Carbon::now();
        $log->message = $message;
        $log->type = $type;
        $log->export_id = $id;
        $log->save();
        
        return true;
    }
}
