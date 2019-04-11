<?php

namespace App\Http\Controllers\DataProvider;

use App\Http\Controllers\Controller;
use app\Libraries\Traits\Cache\CacheKeyTrait;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class TickerController extends Controller
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

    public function getTicker(Request $request){
        $erc20_obj = $request->get('erc20_obj');

        if(is_null($ticker_obj = Cache::get($this->getTickerCacheKey($erc20_obj)))) {
            $token_symbol = 'qtum';
            if (!is_null($erc20_obj)) {
                // erc20
                $token_symbol = $erc20_obj->erc20_symbol;

                if(strtolower($token_symbol) == 'spc'){
                    $token_symbol = 'spacechain';
                }
                if(strtolower($token_symbol) == 'oc'){
                    $token_symbol = 'oceanchain';
                }
            }

            $client_obj = new Client();
            $response_obj = $client_obj->get('https://api.coinmarketcap.com/v1/ticker/' . $token_symbol . '/');
            //$response_obj = $client_obj->get('https://api.coinmarketcap.com/v1/ticker/ink/');
            $body = $response_obj->getBody();
            $contents = $body->getContents();
            $ticker_obj = (array)json_decode($contents, true);

            Cache::put($this->getTickerCacheKey($erc20_obj), $ticker_obj, config('cache.expires'));
        }

        return response()->json([
            'ticker_obj' => $ticker_obj[0],
        ], 200);
    }
}
