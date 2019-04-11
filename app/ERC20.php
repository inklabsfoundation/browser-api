<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ERC20 extends Model
{
    use SoftDeletes;
    protected $table = 'erc20';
    protected $primaryKey = 'erc20_id';

    public function addERC20(string $erc20_address_qtum, string $erc20_address_hex, string $creator_address_qtum, string $creator_address_hex, string $tx_id, string $block_hash, string $erc20_name, string $erc20_symbol, string $erc20_total_supply, int $erc20_decimal){
        $this->erc20_address_qtum = $erc20_address_qtum;
        $this->erc20_address_hex = $erc20_address_hex;
        $this->creator_address_qtum = $creator_address_qtum;
        $this->creator_address_hex = $creator_address_hex;
        $this->tx_id = $tx_id;
        $this->block_hash = $block_hash;
        $this->erc20_name = $erc20_name;
        $this->erc20_symbol = $erc20_symbol;
        $this->erc20_total_supply = $erc20_total_supply;
        $this->erc20_decimal = $erc20_decimal;

        if(!$this->isDirty() || $this->save()){
            return $this->{$this->primaryKey};
        }else{
            return false;
        }
    }

    public function erc20_transaction_obj_arr(){
        $this->hasMany('App\ERC20Transaction', 'erc20_id', 'erc20_id');
    }

    public function getERC20ByAddressHex(string $erc20_address_hex){
        return $this->where('erc20_address_hex', '=', $erc20_address_hex)->firstOrFail();
    }
}
