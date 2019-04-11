<?php

namespace app\Libraries\Classes;

use GuzzleHttp\Client;

/**
 * Class Qtum
 *
 * @package \app\Libraries\Classes
 */
class Qtum
{

    public static function getChainInfo() :array {
        return (array)self::requestQtum('getinfo', [])['result'];
    }

    public static function getBlockCount() :int {
        return (int)self::requestQtum('getblockcount', [])['result'];
    }

    public static function getBestBlockHash() :string {
        return (string)self::requestQtum('getbestblockhash', [])['result'];
    }

    public static function getBlockHash(int $block_height) :string {
        return (string)self::requestQtum('getblockhash', [$block_height])['result'];
    }

    public static function getBlockInfo(string $block_hash) :array {
        return (array)self::requestQtum('getblock', [$block_hash])['result'];
    }

    public static function getRawTransaction(string $transaction_hash) :string {
        return (string)self::requestQtum('getrawtransaction', [$transaction_hash])['result'];
    }

    public static function decodeRawTransaction(string $raw_transaction) :array {
        return (array)self::requestQtum('decoderawtransaction', [$raw_transaction])['result'];
    }

    public static function getTransactionReceipt(string $transaction_hash) :array {
        return (array)self::requestQtum('gettransactionreceipt', [$transaction_hash])['result'];
    }

    public static function callContract(string $contract_address_hex, string $data_hex, string $caller_address_qtum) :array {
        return (array)self::requestQtum('callcontract', [$contract_address_hex, $data_hex, $caller_address_qtum])['result'];
    }

    public static function getAddressERC20Balance(string $erc20_address_hex, string $address_hex) :string {
        $balance = ltrim(self::callContract($erc20_address_hex, '70a08231000000000000000000000000'.$address_hex, '')['executionResult']['output'], '0');
        if(empty($balance)){
            $balance = '0';
        }
        return $balance;
    }
    public static function getERC20Name(string $erc20_address_hex) :string {
        return escape_hidden_char(trim(hex2bin(self::callContract($erc20_address_hex, '06fdde03', '')['executionResult']['output'])));
    }
    public static function getERC20Symbol(string $erc20_address_hex) :string {
        return escape_hidden_char(trim(hex2bin(self::callContract($erc20_address_hex, '95d89b41', '')['executionResult']['output'])));
    }
    public static function getERC20TotalSupply(string $erc20_address_hex) :string {
        return ltrim(self::callContract($erc20_address_hex, '18160ddd', '')['executionResult']['output'], '0');
    }
    public static function getERC20Decimal(string $erc20_address_hex) :?int {
        $result = self::callContract($erc20_address_hex, '313ce567', '')['executionResult']['output'];
        if ($result == ''){
            return null;
        }
        return bchexdec(ltrim($result, '0'));
    }



    public static function validateAddress(string $address) :array {
        return (array)self::requestQtum('validateaddress', [$address])['result'];
    }

    private static function _getHexAddress(string $address_qtum) :string {
        return (string)self::requestQtum('gethexaddress', [$address_qtum])['result'];
    }

    public static function getHexAddress(string $address_qtum) {
        $validate_address = self::validateAddress($address_qtum);
        if($validate_address['isvalid'] != true || $validate_address['isscript'] != false){
            return null;
        }
        return self::_getHexAddress($address_qtum);
    }

    public static function fromHexAddress(string $address_hex) :string {
        return (string)self::requestQtum('fromhexaddress', [$address_hex])['result'];
    }



    private static function requestQtum(string $method, array $param_arr) :array {
        $client_obj = new Client();
        $response_obj = $client_obj->post(config('qtumd.host'), [
            //'auth' => ['qtum', 'password'],
            'auth' => [config('qtumd.rpcuser'), config('qtumd.rpcpassword')], //@todo env
            'json' => [
                'jsonrpc' => '1.0',
                'id' => 'info_ink',
                'method' => $method,
                'params' => $param_arr,
            ]
        ]);

        $body = $response_obj->getBody();

        $contents = $body->getContents();

        return (array)json_decode($contents, true);
    }
}