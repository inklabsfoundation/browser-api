<?php

namespace App\Http\Controllers\Block;

use App\Block;
use App\Http\Controllers\Controller;
use app\Libraries\Traits\Cache\CacheKeyTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class BlockController extends Controller
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

    public function getLatest(Request $request){
        $erc20_obj = $request->get('erc20_obj');

        if(is_null($block_obj = Cache::get($this->getLatestBlockCacheKey($erc20_obj)))) {

            $block_obj = (new Block)->getLatestBlock();

            Cache::put($this->getLatestBlockCacheKey($erc20_obj), $block_obj, config('cache.expires'));
        }

        return response()->json([
            'block_obj' => $block_obj,
        ], 200);
    }


    public function getBlockList(Request $request, $per_page = 10){
        $erc20_obj = $request->get('erc20_obj');
        $page_number = $request->get('page');

        if(is_null($block_obj_arr = Cache::get($this->getBlockListCacheKey($erc20_obj, $per_page, $page_number)))) {

            $block_obj_arr = (new Block)->getBlockArrPaginate($per_page, $erc20_obj);

            Cache::put($this->getBlockListCacheKey($erc20_obj, $per_page, $page_number), $block_obj_arr, config('cache.expires'));
        }
        $block_obj_arr->setPath('https://' . $request->getHttpHost() . '/' . $request->path());

        return response()->json([
            'block_obj_arr' => $block_obj_arr,
        ], 200);
    }


    public function getBlockInfo(Request $request, string $block_hash){
        $erc20_obj = $request->get('erc20_obj');

        if(is_null($block_obj = Cache::get($this->getBlockInfoCacheKey($erc20_obj, $block_hash)))) {

            $block_obj = (new Block)->getBlockInfo($block_hash, $request->get('erc20_obj'));

            Cache::put($this->getBlockInfoCacheKey($erc20_obj, $block_hash), $block_obj, config('cache.expires'));
        }

        return response()->json([
            'block_obj' => $block_obj,
        ], 200);
    }
}
