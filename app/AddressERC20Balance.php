<?php

namespace App;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class AddressERC20Balance extends Model
{
    protected $table = 'address_erc20_balance';
    protected $primaryKey = null;
    public $incrementing = false;

    public function addAddressERC20Balance(string $address_qtum, string $address_hex, int $erc20_id, string $erc20_symbol, string $erc20_balance_hex, int $updated_at_block_height, string $updated_at_block_hash){
        $this->address_qtum = $address_qtum;
        $this->address_hex = $address_hex;
        $this->erc20_id = $erc20_id;
        $this->erc20_symbol = $erc20_symbol;
        $this->erc20_balance = $erc20_balance_hex;
        $this->updated_at_block_height = $updated_at_block_height;
        $this->updated_at_block_hash = $updated_at_block_hash;

        if(!$this->isDirty() || $this->save()){
            return $this->address_qtum;
        }else{
            return false;
        }
    }

    public function erc20_transaction_obj_arr(){
        $this->hasMany('App\ERC20Transaction', 'erc20_id', 'erc20_id');
    }


    public function updateAddressERC20Balance(string $address_hex, int $erc20_id, string $erc20_balance_hex, int $updated_at_block_height, string $updated_at_block_hash){
        $address_obj = $this->getAddressERC20BalanceByAddressHexAndERC20Id($address_hex, $erc20_id);

        $address_obj->where('address_hex', '=', $address_hex)
            ->where('erc20_id', '=', $erc20_id)
            ->update([
                'erc20_balance' => $erc20_balance_hex,
                'updated_at_block_height' => $updated_at_block_height,
                'updated_at_block_hash' => $updated_at_block_hash,
                'updated_at' => Carbon::now(),
            ]);

        return true;
    }

    public function getAddressERC20BalanceByAddressHexAndERC20Id(string $address_hex, int $erc20_id){
        return $this->where('address_hex', '=', $address_hex)
            ->where('erc20_id', '=', $erc20_id)
            ->firstOrFail();
    }


    public function getHeldAddressAmountByERC20Id(int $erc20_id) :int {
        return $this->where('erc20_id', '=', $erc20_id)
            ->where('erc20_balance', '!=', '0')
            ->count();
    }

    public function getHeldAddressArrByERC20IdPaginate(int $erc20_id, int $per_page) {
        return $this->where('erc20_id', '=', $erc20_id)
            ->where('erc20_balance', '!=', '0')
            ->orderByRaw('CONVERT(CONV(erc20_balance, 16, 10), UNSIGNED) DESC')
            ->paginate($per_page);
    }
}
