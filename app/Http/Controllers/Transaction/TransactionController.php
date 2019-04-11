<?php

namespace App\Http\Controllers\Transaction;

use App\AddressTransaction;
use App\ERC20Transaction;
use App\Http\Controllers\Controller;
use app\Libraries\Traits\Cache\CacheKeyTrait;
use App\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class TransactionController extends Controller
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

    public function getTotalAmount(Request $request){
        $erc20_obj = $request->get('erc20_obj');

        if(is_null($transaction_amount = Cache::get($this->getTransactionTotalAmountCacheKey($erc20_obj)))) {

            $transaction_amount = (new Transaction)->getTotalAmount($erc20_obj);

            Cache::put($this->getTransactionTotalAmountCacheKey($erc20_obj), $transaction_amount, config('cache.expires'));
        }

        return response()->json([
            'transaction_amount' => $transaction_amount,
        ], 200);
    }

    public function getAmountByDays(Request $request, int $days = 14){
        $erc20_obj = $request->get('erc20_obj');

        if(is_null($transaction_amount_arr = Cache::get($this->getTransactionAmountByDaysCacheKey($erc20_obj, $days)))) {

            $transaction_amount_arr = (new Transaction)->getAmountByDays($days, $request->get('erc20_obj'));

            Cache::put($this->getTransactionAmountByDaysCacheKey($erc20_obj, $days), $transaction_amount_arr, config('cache.expires'));
        }

        return response()->json([
            'transaction_amount_arr' => $transaction_amount_arr,
        ], 200);
    }

    public function getTransactionList(Request $request, $per_page = 10){
        $erc20_obj = $request->get('erc20_obj');
        $page_number = $request->get('page');

        if(is_null($transaction_obj_arr = Cache::get($this->getTransactionListCacheKey($erc20_obj, $per_page, $page_number)))) {

            $transaction_obj_arr = (new Transaction)->getTransactionArr($per_page, $request->get('erc20_obj'));

            Cache::put($this->getTransactionListCacheKey($erc20_obj, $per_page, $page_number), $transaction_obj_arr, config('cache.expires'));
        }
        $transaction_obj_arr->setPath('https://' . $request->getHttpHost() . '/' . $request->path());


        return response()->json([
            'transaction_arr' => $transaction_obj_arr,
        ], 200);
    }

    public function getTransactionInfo(Request $request, string $transaction_hash){
        $erc20_obj = $request->get('erc20_obj');

        if(is_null($transaction_obj = Cache::get($this->getTransactionInfoCacheKey($erc20_obj, $transaction_hash)))) {

            $transaction_obj = (new Transaction)->getTransactionInfo($transaction_hash, $request->get('erc20_obj'));

            Cache::put($this->getTransactionInfoCacheKey($erc20_obj, $transaction_hash), $transaction_obj, config('cache.expires'));
        }

        return response()->json([
            'transaction_obj' => $transaction_obj,
        ], 200);
    }

    public function getBlockTransactionList(Request $request, string $block_hash, int $per_page = 10){
        $erc20_obj = $request->get('erc20_obj');
        $page_number = $request->get('page');

        if(is_null($transaction_obj_arr = Cache::get($this->getBlockTransactionListCacheKey($erc20_obj, $block_hash, $per_page, $page_number)))) {

            $transaction_obj_arr = (new Transaction)->getTransactionArrByBlockHashPaginate($block_hash, $per_page, $erc20_obj);

            Cache::put($this->getBlockTransactionListCacheKey($erc20_obj, $block_hash, $per_page, $page_number), $transaction_obj_arr, config('cache.expires'));
        }
        $transaction_obj_arr->setPath('https://' . $request->getHttpHost() . '/' . $request->path());


        return response()->json([
            'transaction_arr' => $transaction_obj_arr,
        ], 200);
    }

    public function getAddressTransactionList(Request $request, string $address_qtum, int $per_page = 10){
        $erc20_obj = $request->get('erc20_obj');
        $page_number = $request->get('page');

        if(is_null($transaction_obj_arr = Cache::get($this->getAddressTransactionListCacheKey($erc20_obj, $address_qtum, $per_page, $page_number)))) {

            if(is_null($erc20_obj)){
                $transaction_obj_arr = (new AddressTransaction)->getTransactionArrByAddressQtumPaginate($address_qtum, $per_page, $erc20_obj);
            }else{
                $transaction_obj_arr = (new ERC20Transaction)->getAddressERC20TransactionArrPaginate($address_qtum, $per_page, $erc20_obj);
            }

            Cache::put($this->getAddressTransactionListCacheKey($erc20_obj, $address_qtum, $per_page, $page_number), $transaction_obj_arr, config('cache.expires'));
        }
        $transaction_obj_arr->setPath('https://' . $request->getHttpHost() . '/' . $request->path());


        return response()->json([
            'transaction_arr' => $transaction_obj_arr,
        ], 200);
    }

}
