<?php

namespace App\Jobs;

use App\Address;
use App\AddressERC20Balance;
use App\Block;
use App\ERC20;
use app\Libraries\Traits\Cache\CacheKeyTrait;
use App\Transaction;
use Illuminate\Support\Facades\Cache;

class WebCacheJob extends Job
{
    use CacheKeyTrait;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        // cache index data to redis
        $erc20_address_arr = ['fe59cbc1704e89a698571413a81f0de9d8f00c69', '57931faffdec114056a49adfcaa1caac159a1a25'];
        foreach($erc20_address_arr as $erc20_address_hex) {
//        1) "laravel:GLOBAL::ERC20_OBJ::fe59cbc1704e89a698571413a81f0de9d8f00c69"
            $erc20_obj = (new ERC20)->getERC20ByAddressHex($erc20_address_hex);


//        1) "laravel:INDEX::LATEST_BLOCK::fe59cbc1704e89a698571413a81f0de9d8f00c69"
            $block_obj = (new Block)->getLatestBlock();
            Cache::put($this->getLatestBlockCacheKey($erc20_obj), $block_obj, 10);

//        3) "laravel:INDEX::TRANSACTION_AMOUNT_BY_DAYS::fe59cbc1704e89a698571413a81f0de9d8f00c69::14"
            $days = 14;
            $transaction_amount_arr = (new Transaction)->getAmountByDays($days, $erc20_obj);
            Cache::put($this->getTransactionAmountByDaysCacheKey($erc20_obj, $days), $transaction_amount_arr, 10);

//        4) "laravel:BLOCK::LIST::fe59cbc1704e89a698571413a81f0de9d8f00c69::10::1"
            $per_page = 1;
            $page_number = 1;
            $block_obj_arr = (new Block)->getBlockArrPaginate($per_page, $erc20_obj);
            Cache::put($this->getBlockListCacheKey($erc20_obj, $per_page, $page_number), $block_obj_arr, 10);

//        5) "laravel:INDEX::HELD_ADDRESS_AMOUNT::fe59cbc1704e89a698571413a81f0de9d8f00c69"
            if (!is_null($erc20_obj)) {
                // erc20
                $address_amount = (new AddressERC20Balance)->getHeldAddressAmountByERC20Id($erc20_obj->erc20_id);
            } else {
                // qtum
                $address_amount = (new Address)->getHeldAddressAmount();
            }
            Cache::put($this->getHeldAddressAmountCacheKey($erc20_obj), $address_amount, 10);


//        6) "laravel:INDEX::TRANSACTION_TOTAL_AMOUNT::fe59cbc1704e89a698571413a81f0de9d8f00c69"
            $transaction_amount = (new Transaction)->getTotalAmount($erc20_obj);
            Cache::put($this->getTransactionTotalAmountCacheKey($erc20_obj), $transaction_amount, 10);
        }
    }
}
