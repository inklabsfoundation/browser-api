<?php

namespace app\Libraries\Traits\Cache;

use App\ERC20;

trait CacheKeyTrait
{
    private $ERC20_OBJ = 'GLOBAL::ERC20_OBJ::%s';

    private $HELD_ADDRESS_AMOUNT = 'INDEX::HELD_ADDRESS_AMOUNT::%s';
    private $LATEST_BLOCK = 'INDEX::LATEST_BLOCK::%s';
    private $TICKER = 'INDEX::TICKER::%s';
    private $TRANSACTION_TOTAL_AMOUNT = 'INDEX::TRANSACTION_TOTAL_AMOUNT::%s';
    private $TRANSACTION_AMOUNT_BY_DAYS = 'INDEX::TRANSACTION_AMOUNT_BY_DAYS::%s::%d';


    private $BLOCK_LIST = 'BLOCK::LIST::%s::%d::%d';
    private $BLOCK_INFO = 'BLOCK::INFO::%s::%s';
    private $BLOCK_TRANSACTION_LIST = 'BLOCK::TRANSACTION::LIST::%s::%s::%d::%d';

    private $TRANSACTION_LIST = 'TRANSACTION::LIST::%s::%d::%d';
    private $TRANSACTION_INFO = 'TRANSACTION::INFO::%s::%s';


    private $ADDRESS_INFO = 'ADDRESS::INFO::%s::%s';
    private $ADDRESS_TRANSACTION_LIST = 'ADDRESS::TRANSACTION::LIST::%s::%s::%d::%d';
    private $ADDRESS_HOLDER_LIST = 'ADDRESS::HOLDER::LIST::%s::%d::%d';


    private function getERC20ObjCacheKey(string $erc20_address_hex): string
    {
        return sprintf($this->ERC20_OBJ, $erc20_address_hex);
    }

    private function getHeldAddressAmountCacheKey(ERC20 $erc20_obj = null): string
    {
        return sprintf($this->HELD_ADDRESS_AMOUNT, isset($erc20_obj) ? $erc20_obj->erc20_address_hex : '-');
    }

    private function getLatestBlockCacheKey(ERC20 $erc20_obj = null): string
    {
        return sprintf($this->LATEST_BLOCK, isset($erc20_obj) ? $erc20_obj->erc20_address_hex : '-');
    }

    private function getTickerCacheKey(ERC20 $erc20_obj = null): string
    {
        return sprintf($this->TICKER, isset($erc20_obj) ? $erc20_obj->erc20_address_hex : '-');
    }

    private function getTransactionTotalAmountCacheKey(ERC20 $erc20_obj = null): string
    {
        return sprintf($this->TRANSACTION_TOTAL_AMOUNT, isset($erc20_obj) ? $erc20_obj->erc20_address_hex : '-');
    }

    private function getTransactionAmountByDaysCacheKey(ERC20 $erc20_obj = null, int $days): string
    {
        return sprintf($this->TRANSACTION_AMOUNT_BY_DAYS, isset($erc20_obj) ? $erc20_obj->erc20_address_hex : '-', $days);
    }


    private function getBlockListCacheKey(ERC20 $erc20_obj = null, int $per_page, int $page_number = null): string
    {
        return sprintf($this->BLOCK_LIST, isset($erc20_obj) ? $erc20_obj->erc20_address_hex : '-', $per_page, isset($page_number) ? $page_number : 1);
    }

    private function getBlockInfoCacheKey(ERC20 $erc20_obj = null, string $block_hash): string
    {
        return sprintf($this->BLOCK_INFO, isset($erc20_obj) ? $erc20_obj->erc20_address_hex : '-', $block_hash);
    }

    private function getBlockTransactionListCacheKey(ERC20 $erc20_obj = null, string $block_hash, int $per_page, int $page_number = null): string
    {
        return sprintf($this->BLOCK_TRANSACTION_LIST, isset($erc20_obj) ? $erc20_obj->erc20_address_hex : '-', $block_hash, $per_page, isset($page_number) ? $page_number : 1);
    }


    private function getTransactionListCacheKey(ERC20 $erc20_obj = null, int $per_page, int $page_number = null): string
    {
        return sprintf($this->TRANSACTION_LIST, isset($erc20_obj) ? $erc20_obj->erc20_address_hex : '-', $per_page, isset($page_number) ? $page_number : 1);
    }

    private function getTransactionInfoCacheKey(ERC20 $erc20_obj = null, string $transaction_hash): string
    {
        return sprintf($this->TRANSACTION_INFO, isset($erc20_obj) ? $erc20_obj->erc20_address_hex : '-', $transaction_hash);
    }


    private function getAddressInfoCacheKey(ERC20 $erc20_obj = null, string $address_qtum): string
    {
        return sprintf($this->ADDRESS_INFO, isset($erc20_obj) ? $erc20_obj->erc20_address_hex : '-', $address_qtum);
    }

    private function getAddressTransactionListCacheKey(ERC20 $erc20_obj = null, string $address_qtum, int $per_page, int $page_number = null): string
    {
        return sprintf($this->ADDRESS_TRANSACTION_LIST, isset($erc20_obj) ? $erc20_obj->erc20_address_hex : '-', $address_qtum, $per_page, isset($page_number) ? $page_number : 1);
    }

    private function getHolderAddressListCacheKey(ERC20 $erc20_obj = null, int $per_page, int $page_number = null): string
    {
        return sprintf($this->ADDRESS_HOLDER_LIST, isset($erc20_obj) ? $erc20_obj->erc20_address_hex : '-', $per_page, isset($page_number) ? $page_number : 1);
    }


}