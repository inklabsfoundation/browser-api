<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Address extends Model
{
    protected $table = 'address';
    protected $primaryKey = 'address_qtum';
    public $incrementing = false;

    public function addAddress(string $address_qtum, string $address_hex = null, string $qtum_balance_dec, int $updated_at_block_height, string $updated_at_block_hash){
        $this->address_qtum = $address_qtum;
        $this->address_hex = $address_hex;
        $this->qtum_balance = bcdechex($qtum_balance_dec);
        $this->updated_at_block_height = $updated_at_block_height;
        $this->updated_at_block_hash = $updated_at_block_hash;

        if(!$this->isDirty() || $this->save()){
            return $this->{$this->primaryKey};
        }else{
            return false;
        }
    }

    public function address_transaction_obj_arr(){
        return $this->hasMany('App\AddressTransaction', 'address_qtum', 'address_qtum');
    }

    public function updateAddressQtumBalance(string $address_qtum, string $qtum_balance_dec, int $updated_at_block_height, string $updated_at_block_hash){
        $address_obj = $this->getAddressByAddressQtum($address_qtum);

        $address_obj->qtum_balance = bcdechex(bcadd(bchexdec($address_obj->qtum_balance), $qtum_balance_dec));
        $address_obj->updated_at_block_height = $updated_at_block_height;
        $address_obj->updated_at_block_hash = $updated_at_block_hash;

        if(!$address_obj->isDirty() || $address_obj->save()){
            return $address_obj->{$this->primaryKey};
        }else{
            return false;
        }
    }

    public function getAddressByAddressQtum(string $address_qtum){
        return $this->where('address_qtum', '=', $address_qtum)->firstOrFail();
    }

    public function getHeldAddressAmount() :int {
        return $this->where('qtum_balance', '!=', '0')->count();
    }

    public function getHeldAddressArrPaginate(int $per_page) {
        return $this->where('qtum_balance', '!=', '0')
            ->orderByRaw('CONVERT(CONV(qtum_balance, 16, 10), UNSIGNED) DESC')
            ->paginate($per_page);
    }

    public function getAddressInfo(string $address_qtum, ERC20 $erc20_obj = null){
        $tmp = $this;

        if(!is_null($erc20_obj)){
            $tmp = $tmp->rightJoin('address_erc20_balance', 'address_erc20_balance.address_qtum', '=', 'address.address_qtum') // erc20 balance 有的, qtum balance 不一定有, qtum balance 有的, 不一定需要获取erc20 balance
                ->where('address_erc20_balance.erc20_id', '=', $erc20_obj->erc20_id)
                ->where('address_erc20_balance.address_qtum', '=', $address_qtum);
        }else{
            $tmp = $tmp->where('address.address_qtum', '=', $address_qtum);
        }

        return $tmp->firstOrFail();
    }
}
