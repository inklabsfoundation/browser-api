<?php

use Laravel\Lumen\Testing\DatabaseMigrations;
use Laravel\Lumen\Testing\DatabaseTransactions;

use \app\Libraries\Classes\Qtum;

class QtumTest extends TestCase
{
    /**
     * A basic test example.
     *
     * @return void
     */

//    public function testGetChainInfo(){
//        $chain_obj = Qtum::getChainInfo();
//
//        $this->assertTrue(is_array($chain_obj), $chain_obj);
//
//        var_dump($chain_obj);
//    }
//
//    public function testGetBlockCount()
//    {
//        $block_count = Qtum::getBlockCount();
//
//        $this->assertTrue(is_int($block_count), $block_count);
//
//        var_dump($block_count);
//    }
//
//    public function testGetBestBlockHash()
//    {
//        $block_hash = Qtum::getBestBlockHash();
//
//        $this->assertTrue(is_string($block_hash), $block_hash);
//
//        var_dump($block_hash);
//    }
//
//    public function testGetBlockHash(){
//        $block_count = Qtum::getBlockCount();
//        $block_hash = Qtum::getBlockHash($block_count);
//
//        $this->assertTrue(is_string($block_hash), $block_hash);
//
//        var_dump($block_hash);
//    }
//
    public function testGetBlockInfo(){
        $block_count = Qtum::getBlockCount();
        $block_hash = Qtum::getBlockHash($block_count);
        $block_obj = Qtum::getBlockInfo($block_hash);

        $this->assertTrue(is_array($block_obj), $block_obj);

        var_dump($block_obj);
    }
//
//    public function testGetRawTransaction(){
//        $block_count = Qtum::getBlockCount();
//        $block_hash = Qtum::getBlockHash($block_count);
//        $block_obj = Qtum::getBlockInfo($block_hash);
//
//
//        //$raw_transaction = Qtum::getRawTransaction($block_obj['tx'][0]); // mined from coinbase
//        $raw_transaction = Qtum::getRawTransaction($block_obj['tx'][1]);
//
//        $this->assertTrue(is_string($raw_transaction), $raw_transaction);
//
//        var_dump($raw_transaction);
//
//
//        $transaction_obj = Qtum::decodeRawTransaction($raw_transaction);
//
//        $this->assertTrue(is_array($transaction_obj), $transaction_obj);
//
//        var_dump($transaction_obj);
//
//
//        $transaction_receipt_obj = Qtum::getTransactionReceipt($block_obj['tx'][1]);
//
//        $this->assertTrue(is_array($transaction_receipt_obj), $transaction_receipt_obj);
//
//        var_dump($transaction_receipt_obj);
//    }

    public function testGetTransactionReceipt(){
        $transaction_receipt_obj = Qtum::getTransactionReceipt('f4a5d7c731a0e8aa7ea69cebe843cb778faa94ce91fb4c44df86ae4759dfcdf3');

        $this->assertTrue(is_array($transaction_receipt_obj), $transaction_receipt_obj);

        var_dump($transaction_receipt_obj);
    }
//
//
//    public function testValidateAddress(){
//        $address = 'QRBLBKGA8tbPmXcbsStTXUewmu2vCffE3J'; // MVE8V2tw7LthPf1CofRkH1DspXFkLnbhvD 多重签名
//        $valid_address = Qtum::validateAddress('QRBLBKGA8tbPmXcbsStTXUewmu2vCffE3J');
//
//        $this->assertTrue(is_array($valid_address), $valid_address);
//
//        var_dump($address);
//
//
//
//        $address = 'QPSSGeFHDnKNxiEyFrD1wcEaHr9hrQDDWc';
//        $invalid_address = Qtum::validateAddress('QPSSGeFHDnKNxiEyFrD1wcEaHr9hrQDDWc');
//
//        $this->assertFalse($invalid_address, $address);
//
//        var_dump($address);
//    }
//
//    public function testGetHexAddress(){
//        $address_qtum = 'QRBLBKGA8tbPmXcbsStTXUewmu2vCffE3J';
//        $address_hex = Qtum::getHexAddress($address_qtum);
//        $address_qtum_return = Qtum::fromHexAddress($address_hex);
//
//        $this->assertEquals($address_qtum, $address_qtum_return, [$address_qtum, $address_hex]);
//
//        var_dump($address_qtum, $address_hex);
//    }
//
//    public function testGetAddressERC20Balance(){
//        $contract_address_hex = 'fe59cbc1704e89a698571413a81f0de9d8f00c69'; // ink
//        $decimal = 9;
//        $address_qtum = 'QWqC1JoU5ZB15ju4r4A7u21VT3C2UUmacS';
//        $address_hex = Qtum::getHexAddress($address_qtum);
//        $balance_hex = Qtum::getAddressERC20Balance($contract_address_hex, $address_hex);
//
//        $this->assertTrue(is_string($balance_hex), $balance_hex);
//
//
//        var_dump($balance_hex, bcdiv(bchexdec($balance_hex), pow(10, $decimal), $decimal));
//    }
//
    public function testGetERC20Info(){
        $erc20_address_hex = '0e18f7ccb62eaa5413fcceb4bdf71a2f0b9f3506'; // 'fe59cbc1704e89a698571413a81f0de9d8f00c69';

        $erc20_name = Qtum::getERC20Name($erc20_address_hex);
        $erc20_symbol = Qtum::getERC20Symbol($erc20_address_hex);
        $erc20_total_supply = Qtum::getERC20TotalSupply($erc20_address_hex);
        $erc20_decimal = Qtum::getERC20Decimal($erc20_address_hex);

        $this->assertTrue(is_string($erc20_name), $erc20_name);
        $this->assertTrue(is_string($erc20_symbol), $erc20_symbol);
        $this->assertTrue(is_string($erc20_total_supply), $erc20_total_supply);
        $this->assertTrue(is_int($erc20_decimal) || is_null($erc20_decimal), $erc20_decimal);

        var_dump($erc20_name, $erc20_symbol, $erc20_total_supply, $erc20_decimal);
    }
//
//    public function testGetRawTransaction1(){
//
//
//        //$raw_transaction = Qtum::getRawTransaction($block_obj['tx'][0]); // mined from coinbase
//        $raw_transaction = Qtum::getRawTransaction('e34fa2c3b6f2c48d868c69a8e5e4fde9ab23a59d89129ea965e17e14a81160b1');
//
//        $this->assertTrue(is_string($raw_transaction), $raw_transaction);
//
//        var_dump($raw_transaction);
//
//
//        $transaction_obj = Qtum::decodeRawTransaction($raw_transaction);
//
//        $this->assertTrue(is_array($transaction_obj), $transaction_obj);
//
//        var_dump($transaction_obj);
//
//    }
}
