<?php

namespace App\Http\Controllers\Address;

use App\Address;
use App\AddressERC20Balance;
use App\Http\Controllers\Controller;
use app\Libraries\Traits\Cache\CacheKeyTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class AddressController extends Controller
{
    use CacheKeyTrait;
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    public function getHeldAddressAmount(Request $request){
        $erc20_obj = $request->get('erc20_obj');

        if(is_null($address_amount = Cache::get($this->getHeldAddressAmountCacheKey($erc20_obj)))) {
            if (!is_null($erc20_obj)) {
                // erc20
                $address_amount = (new AddressERC20Balance)->getHeldAddressAmountByERC20Id($erc20_obj->erc20_id);
            }else{
                // qtum
                $address_amount = (new Address)->getHeldAddressAmount();
            }

            Cache::put($this->getHeldAddressAmountCacheKey($erc20_obj), $address_amount, config('cache.expires'));
        }

        return response()->json([
            'address_amount' => $address_amount,
        ], 200);
    }

    public function getAddressInfo(Request $request, string $address_qtum){
        $erc20_obj = $request->get('erc20_obj');

        if(is_null($address_obj = Cache::get($this->getAddressInfoCacheKey($erc20_obj, $address_qtum)))) {

            $address_obj = (new Address)->getAddressInfo($address_qtum, $erc20_obj);

            Cache::put($this->getAddressInfoCacheKey($erc20_obj, $address_qtum), $address_obj, config('cache.expires'));
        }

        return response()->json([
            'address_obj' => $address_obj
        ], 200);
    }

    public function getHolderAddressList(Request $request, int $per_page = 10){
        $erc20_obj = $request->get('erc20_obj');
        $page_number = $request->get('page');

        if(is_null($address_obj_arr = Cache::get($this->getHolderAddressListCacheKey($erc20_obj, $per_page, $page_number)))) {
            if (!is_null($erc20_obj)) {
                // erc20
                $address_obj_arr = (new AddressERC20Balance)->getHeldAddressArrByERC20IdPaginate($erc20_obj->erc20_id, $per_page);
            }else{
                // qtum
                $address_obj_arr = (new Address)->getHeldAddressArrPaginate($per_page);
            }

            Cache::put($this->getHolderAddressListCacheKey($erc20_obj, $per_page, $page_number), $address_obj_arr, config('cache.expires'));
        }
        $address_obj_arr->setPath('https://' . $request->getHttpHost() . '/' . $request->path());


        return response()->json([
            'address_obj_arr' => $address_obj_arr,
        ], 200);
    }
}
