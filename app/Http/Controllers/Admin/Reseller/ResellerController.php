<?php

namespace App\Http\Controllers\Admin\Reseller;

use App\Contracts\Repositories\BusinessSettingRepositoryInterface;
use App\Contracts\Repositories\ResellerRepositoryInterface;
use App\Contracts\Repositories\OrderRepositoryInterface;
use App\Contracts\Repositories\SubscriptionRepositoryInterface;
use App\Contracts\Repositories\TranslationRepositoryInterface;
use App\Enums\ViewPaths\Admin\Reseller;
use App\Enums\ExportFileNames\Admin\Customer as CustomerExport;
use App\Exports\CustomerListExport;
use App\Exports\SubscriberListExport;
use App\Http\Controllers\BaseController;
use App\Http\Requests\Admin\CustomerRequest;
use App\Http\Requests\Admin\CustomerUpdateSettingsRequest;
use App\Services\CustomerService;
use App\Traits\PaginatorTrait;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Validator;
use Illuminate\Contracts\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class ResellerController extends BaseController
{
    use PaginatorTrait;

    public function __construct(
        private readonly ResellerRepositoryInterface $resellerRepo,
        private readonly TranslationRepositoryInterface     $translationRepo,
        private readonly OrderRepositoryInterface           $orderRepo,
        private readonly SubscriptionRepositoryInterface    $subscriptionRepo,
        private readonly BusinessSettingRepositoryInterface $businessSettingRepo,
    )
    {
    }

    /**
     * @param Request|null $request
     * @param string|null $type
     * @return View Index function is the starting point of a controller
     * Index function is the starting point of a controller
     */
    public function index(Request|null $request, string $type = null): View
    {
        return $this->getListView($request);
    }

    public function getListView(Request $request): View
    {
        $customers = $this->resellerRepo->getListWhere(
            searchValue: $request->get('searchValue'),
            dataLimit: getWebConfig(name: 'pagination_limit')
        );
        return view(Reseller::LIST[VIEW], [
            'customers' => $customers,
        ]);
    }

    public function updateStatus(Request $request): RedirectResponse
    {
        $is_active = 0;
        if(isset($request['status'])){
            $this->resellerRepo->update(id: $request['id'], data: ['reseller_status' => $request['status']]);
            if ($request['status'] == "approved") {
                $is_active = 1;
                Toastr::success(translate('Reseller has been approved successfully'));
            } else if ($request['status'] == "rejected") {
                $is_active = 0;
                Toastr::info(translate('Reseller has been rejected successfully'));
            } else if ($request['status'] == "suspended") {
                $is_active = 0;
                $this->resellerRepo->update(id: $request['id'], data: ['auth_token' => Str::random(80)]);
                Toastr::info(translate('Reseller has been suspened successfully'));
            }  
            $this->resellerRepo->update(id: $request['id'], data: ['reseller_status' => $request['status'], 'is_active' => $is_active]);
        }else{
            $validation = Validator::make($request->all(), [
                'sub_domain' => 'required'
            ]);
        
            if ($validation->fails()) {
                $errors = $validation->errors()->all();
                foreach ($errors as $error) {
                    Toastr::error($error);
                }
                return back();
            } 
            
            $this->resellerRepo->update(id: $request['id'], data: [ 'sub_domain' => $request['sub_domain']] );
            Toastr::info(translate('Reseller Info has been updated successfully'));
        }
        
        return back();
    }

    public function getView(Request $request, $id): View|RedirectResponse
    {
        $customer = $this->resellerRepo->getFirstWhere(params: ['id'=>$id]);
        if (isset($customer)) {
            return view(Reseller::VIEW[VIEW], ['seller'=>$customer]);
        }
        Toastr::error(translate('customer_Not_Found'));
        return back();
    }

    /**
     * @param $id
     * @param CustomerService $customerService
     * @return RedirectResponse
     */
    public function delete($id, CustomerService $customerService): RedirectResponse
    {
        $customer = $this->resellerRepo->getFirstWhere(params: ['id'=>$id]);
        $customerService->deleteImage(data:$customer);
        $this->resellerRepo->delete(params:['id'=>$id]);
        Toastr::success(translate('customer_deleted_successfully'));
        return back();
    }

    public function getSubscriberListView(Request $request): View|Application
    {
        $subscription_list = $this->subscriptionRepo->getListWhere(searchValue:$request['searchValue']);
        return view(Customer::SUBSCRIBER_LIST[VIEW], compact('subscription_list'));
    }

    public function exportList(Request $request): BinaryFileResponse
    {
        $customers = $this->resellerRepo->getListWhere(
            searchValue: $request->get('searchValue'),
            filters: ['withCount'=>'orders'],
            relations: ['orders'],
            dataLimit: 'all'
        );
        return Excel::download(new CustomerListExport([
                'customers' => $customers,
                'searchValue' => $request->get('searchValue'),
                'active' => $this->resellerRepo->getListWhere(filters:['is_active'=>1])->count(),
                'inactive' => $this->resellerRepo->getListWhere(filters:['is_active'=>0])->count(),
            ]), CustomerExport::EXPORT_XLSX
        );
    }

    public function exportSubscribersList(Request $request): BinaryFileResponse
    {
        $subscription = $this->subscriptionRepo->getListWhere(searchValue:$request['searchValue'], dataLimit: 'all');
        return Excel::download(new SubscriberListExport([
                'subscription' => $subscription,
                'search' => $request['searchValue'],
            ]), CustomerExport::SUBSCRIBER_LIST_XLSX
        );
    }

    public function getCustomerSettingsView(): View
    {
        $wallet = $this->businessSettingRepo->getListWhere(filters:[['type', 'like', 'wallet_%']]);
        $loyaltyPoint = $this->businessSettingRepo->getListWhere(filters:[['type', 'like', 'loyalty_point_%']]);
        $refEarning = $this->businessSettingRepo->getListWhere(filters:[['type', 'like', 'ref_earning_%']]);

        $data = [];
        foreach ($wallet as $setting) {
            $data[$setting->type] = $setting->value;
        }
        foreach ($loyaltyPoint as $setting) {
            $data[$setting->type] = $setting->value;
        }
        foreach ($refEarning as $setting) {
            $data[$setting->type] = $setting->value;
        }
        return view(Customer::SETTINGS[VIEW], compact('data'));
    }

    public function update(CustomerUpdateSettingsRequest $request): View|RedirectResponse
    {
        if (env('APP_MODE') === 'demo') {
            Toastr::info(translate('update_option_is_disable_for_demo'));
            return back();
        }
        $this->businessSettingRepo->updateOrInsert(type:'wallet_status', value:$request->get('customer_wallet', 0));
        $this->businessSettingRepo->updateOrInsert(type:'loyalty_point_status', value:$request->get('customer_loyalty_point', 0));
        $this->businessSettingRepo->updateOrInsert(type:'wallet_add_refund', value:$request->get('refund_to_wallet', getWebConfig('wallet_add_refund')));
        $this->businessSettingRepo->updateOrInsert(type:'loyalty_point_exchange_rate', value:$request->get('loyalty_point_exchange_rate', getWebConfig('loyalty_point_exchange_rate')));
        $this->businessSettingRepo->updateOrInsert(type:'loyalty_point_item_purchase_point', value:$request->get('item_purchase_point', getWebConfig('loyalty_point_item_purchase_point')));
        $this->businessSettingRepo->updateOrInsert(type:'loyalty_point_minimum_point', value:$request->get('minimun_transfer_point',  getWebConfig('loyalty_point_minimum_point')));
        $this->businessSettingRepo->updateOrInsert(type:'ref_earning_status', value:$request->get('ref_earning_status', 0));
        $this->businessSettingRepo->updateOrInsert(type:'ref_earning_exchange_rate', value:currencyConverter(amount:$request->get('ref_earning_exchange_rate', getWebConfig('ref_earning_exchange_rate'))));
        $this->businessSettingRepo->updateOrInsert(type:'add_funds_to_wallet', value:$request->get('add_funds_to_wallet', getWebConfig('add_funds_to_wallet')));

        if($request->has('minimum_add_fund_amount') && $request->has('maximum_add_fund_amount'))
        {
            if($request['maximum_add_fund_amount'] > $request['minimum_add_fund_amount']){
                $this->businessSettingRepo->updateOrInsert(type:'minimum_add_fund_amount', value:currencyConverter(amount:$request->get('minimum_add_fund_amount', 1)));
                $this->businessSettingRepo->updateOrInsert(type:'maximum_add_fund_amount', value:currencyConverter(amount:$request->get('maximum_add_fund_amount', 0)));
            }else{
                Toastr::error(translate('minimum_amount_cannot_be_greater_than_maximum_amount'));
                return back();
            }
        }

        Toastr::success(translate('customer_settings_updated_successfully'));
        return back();
    }

    public function getCustomerList(Request $request): JsonResponse
    {
        $customers = $this->resellerRepo->getCustomerNameList(
            request:$request,
            dataLimit: getWebConfig(name:'pagination_limit')
        );
        return response()->json($customers);
    }
    public function add(CustomerRequest $request,CustomerService $customerService):RedirectResponse
    {
        $this->resellerRepo->add($customerService->getCustomerData(request: $request));
        Toastr::success(('customer_added_successfully'));
        return redirect()->back();
    }
}