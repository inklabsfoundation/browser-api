<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/

$router->get('/', function () use ($router) {
    //dd(getenv('HOSTNAME'));
    //return $router->app->version();
    return response()->json(['message'=>'Greetings from [INK LABS FOUNDATION] !']);
});
$router->get('/h', function () use ($router) {
    //dd(getenv('HOSTNAME'));
    //return $router->app->version();
    return response()->json(['message'=>'Greetings from [INK LABS FOUNDATION] ! {'.getenv('HOSTNAME').' said.}']);
});
//$router->get('/test', function () use ($router) {
//    return 'Hello Api Backend';
//});

//http://api.info.ink/block/get-latest
//http://api.info.ink/transaction/get-total-amount
//http://api.info.ink/address/get-held-address-amount
//http://api.info.ink/token/get-ticker
//
//http://api.info.ink/transaction/get-14days-chart
//
//http://api.info.ink/block/get-list
//http://api.info.ink/transaction/get-list
//
//http://api.info.ink/block/info/0000d0981d3f9a612628953827ec40ae7ba7cc484b319d13180fd007bc400acb
//http://api.info.ink/transaction/get-block-transaction-list/0000d0981d3f9a612628953827ec40ae7ba7cc484b319d13180fd007bc400acb
//http://api.info.ink/transaction/info/c51333756ee0f715629162bdc71f1b8bfcbdd4b79007be3ee965492f5874f18e
//http://api.info.ink/address/info/QWqC1JoU5ZB15ju4r4A7u21VT3C2UUmacS
//http://api.info.ink/transaction/get-address-transaction-list/QWqC1JoU5ZB15ju4r4A7u21VT3C2UUmacS

$router->group([
    'namespace' => 'Block',
    'middleware' => [App\Http\Middleware\TokenType::class],
], function() use ($router){
    $router->get('/block/get-latest', 'BlockController@getLatest');
    $router->get('/block/get-list', 'BlockController@getBlockList');
    $router->get('/block/info/{block_hash}', 'BlockController@getBlockInfo');
});

$router->group([
    'namespace' => 'Transaction',
    'middleware' => [App\Http\Middleware\TokenType::class],
], function() use ($router){
    $router->get('/transaction/get-total-amount', 'TransactionController@getTotalAmount');
    $router->get('/transaction/get-14days-chart', 'TransactionController@getAmountByDays');
    $router->get('/transaction/get-list', 'TransactionController@getTransactionList');
    $router->get('/transaction/info/{transaction_hash}', 'TransactionController@getTransactionInfo');
    $router->get('/transaction/get-block-transaction-list/{block_hash}', 'TransactionController@getBlockTransactionList');
    $router->get('/transaction/get-address-transaction-list/{address_qtum}', 'TransactionController@getAddressTransactionList');
});
$router->group([
    'namespace' => 'Address',
    'middleware' => [App\Http\Middleware\TokenType::class],
], function() use ($router){
    $router->get('/address/get-held-address-amount', 'AddressController@getHeldAddressAmount');
    $router->get('/address/info/{address_qtum}', 'AddressController@getAddressInfo');
    $router->get('/address/get-held-address-list', 'AddressController@getHolderAddressList');
});

$router->group([
    'namespace' => 'DataProvider',
    'middleware' => [App\Http\Middleware\TokenType::class],
], function() use ($router){
    $router->get('/token/get-ticker', 'TickerController@getTicker');
});