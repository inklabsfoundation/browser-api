<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AddressTransaction extends Model
{
    use SoftDeletes;
    protected $table = 'address_transaction';
    protected $primaryKey = null;
    public $incrementing = false;

    public function addAddressTransaction(string $address_qtum, string $address_hex = null, string $tx_id) :bool {
        $this->address_qtum = $address_qtum;
        $this->address_hex = $address_hex;
        $this->tx_id = $tx_id;

        if(!$this->isDirty() || $this->save()){
            return true;
        }else{
            return false;
        }
    }

    public function address_obj(){
        $this->belongsTo('App\Address', 'address_qtum', 'address_qtum');
    }

    public function transaction_obj(){
        $this->belongsTo('App\Transaction', 'tx_id', 'tx_id');
    }

    public function getTransactionArrByAddressQtumPaginate(string $address_qtum, int $per_page, ERC20 $erc20_obj = null){
        $tmp = $this->join('transaction', 'transaction.tx_id', '=', 'address_transaction.tx_id')
            ->join('block', 'block.block_hash', '=', 'transaction.block_hash');

        if(!is_null($erc20_obj)){
            $tmp->join('erc20_transaction', 'erc20_transaction.tx_id', '=', 'transaction.tx_id')
                ->where('erc20_transaction.erc20_id', '=', $erc20_obj->erc20_id);
        }

        return $tmp->where('address_transaction.address_qtum', '=', $address_qtum)
            ->orderBy('block.block_time', 'desc')
            ->orderBy('transaction.tx_index', 'desc')
            ->paginate($per_page);
    }
}
